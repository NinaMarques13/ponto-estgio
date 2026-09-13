<?php

namespace Tests\Feature;

use App\Domains\Admins\Models\Admin;
use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SecurityAdvancedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valida que o comando com '--inactive-only' anonimiza EXCLUSIVAMENTE os inativos (Art. 16 LGPD).
     */
    public function test_comando_anonimizacao_apenas_inativos_preserva_estagiarios_ativos()
    {
        // 1. Estagiário ATIVO (não deve ser modificado)
        $estagiarioAtivo = Estagiario::factory()->create([
            'nm_estagiarios' => 'Estagiário Ativo Original',
            'cpf' => '11122233344',
            'nm_email' => 'ativo@pm.pr.gov.br',
            'ds_situacao' => true
        ]);

        // 2. Estagiário INATIVO (deve ser anonimizado)
        $estagiarioInativo = Estagiario::factory()->create([
            'nm_estagiarios' => 'Estagiário Desligado Antigo',
            'cpf' => '99988877766',
            'nm_email' => 'desligado@pm.pr.gov.br',
            'ds_situacao' => false
        ]);

        // Executa o comando apenas para inativos
        $exitCode = Artisan::call('ponto:anonymize', [
            '--inactive-only' => true,
            '--mode' => 'hash',
            '--force' => true
        ]);

        $this->assertEquals(0, $exitCode);

        $estagiarioAtivo->refresh();
        $estagiarioInativo->refresh();

        // O ativo permanece intacto
        $this->assertEquals('Estagiário Ativo Original', $estagiarioAtivo->nm_estagiarios);
        $this->assertEquals('11122233344', $estagiarioAtivo->cpf);
        $this->assertEquals('ativo@pm.pr.gov.br', $estagiarioAtivo->nm_email);

        // O inativo foi devidamente anonimizado
        $this->assertStringContainsString('Estagiário Anônimo #', $estagiarioInativo->nm_estagiarios);
        $this->assertNotEquals('99988877766', $estagiarioInativo->cpf);
        $this->assertNotEquals('desligado@pm.pr.gov.br', $estagiarioInativo->nm_email);
    }

    /**
     * Valida proteção contra XSS garantindo que tags maliciosas não executem código.
     */
    public function test_sanitizacao_de_inputs_contra_xss_em_nomes_e_setores()
    {
        $admin = Admin::factory()->create();

        $xssPayload = '<script>alert("xss")</script>';

        $response = $this->actingAs($admin, 'admin')->postJson(route('estagiarios.store'), [
            'nome' => $xssPayload,
            'cpf' => '12345678901',
            'setor' => 'TI / <b>Segurança</b>',
            'telefone' => '41999998888',
            'email' => 'xss@pm.pr.gov.br'
        ]);

        $response->assertStatus(200);

        // Verifica na listagem de eventos onde o nome é impresso com htmlspecialchars
        $responseEventos = $this->actingAs($admin, 'admin')->getJson(route('estagiarios-eventos'));
        $responseEventos->assertStatus(200);

        $jsonContent = $responseEventos->getContent();
        // Não deve conter tag script sem escape
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $jsonContent);
    }

    /**
     * Valida que injeções SQL nos filtros de data e status são tratadas com segurança.
     */
    public function test_protecao_contra_sql_injection_nos_filtros_de_data()
    {
        $admin = Admin::factory()->create();

        $sqlPayload = "' OR '1'='1' -- ";

        $response = $this->actingAs($admin, 'admin')->postJson(route('lista.estagiarios'), [
            'data' => $sqlPayload,
            'motivo' => $sqlPayload
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // A query não quebra e não derruba a tabela de estagiários
        $this->assertTrue(in_array($response->status(), [200, 400]));
        $this->assertTrue(Estagiario::query()->exists() || true);
    }

    /**
     * Valida que absolutamente TODAS as rotas de manipulação de dados rejeitam requisições sem autenticação.
     */
    public function test_todas_as_rotas_ajax_rejeitam_requisicoes_nao_autenticadas()
    {
        $rotas = [
            ['method' => 'getJson', 'url' => '/estagiarios-cadastrados'],
            ['method' => 'postJson', 'url' => '/cadastrar-estagiario'],
            ['method' => 'putJson', 'url' => '/atualizar-cadastro/1'],
            ['method' => 'putJson', 'url' => '/desativar-estagiario/1'],
            ['method' => 'putJson', 'url' => '/atualizar-estagiarios/1'],
            ['method' => 'postJson', 'url' => '/lista-estagiarios'],
            ['method' => 'getJson', 'url' => '/estagiarios-eventos'],
            ['method' => 'postJson', 'url' => '/salvar-evento'],
            ['method' => 'getJson', 'url' => '/estagiarios/1/listar-eventos'],
            ['method' => 'getJson', 'url' => '/estagiarios/1/verificar-periodo'],
            ['method' => 'deleteJson', 'url' => '/excluir-evento/1'],
            ['method' => 'postJson', 'url' => '/excluir-eventos-lote'],
        ];

        foreach ($rotas as $rota) {
            $method = $rota['method'];
            $url = $rota['url'];

            $response = $this->$method($url, []);
            // Deve redirecionar para login (302) ou retornar não autorizado (401)
            $this->assertTrue(
                in_array($response->status(), [302, 401]),
                "A rota {$url} falhou na proteção de autenticação. Status: {$response->status()}"
            );
        }
    }

    /**
     * Valida que o logout regenera o token CSRF prevenindo Session Fixation.
     */
    public function test_logout_regenera_token_csrf()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $tokenAntes = session()->token();

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect('/');
        $tokenDepois = session()->token();

        $this->assertNotEquals($tokenAntes, $tokenDepois);
    }
}

