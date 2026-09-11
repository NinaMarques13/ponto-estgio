<?php

namespace Tests\Feature;

use App\Domains\Admins\Models\Admin;
use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use App\Services\AnonymizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SecurityAndAnonymizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_rotas_administrativas_bloqueadas_para_usuarios_nao_autenticados()
    {
        // 1. Listagem de estagiários cadastrados
        $response = $this->getJson('/estagiarios-cadastrados');
        $this->assertTrue(in_array($response->status(), [401, 302]));

        // 2. Cadastro de estagiário
        $response = $this->postJson('/cadastrar-estagiario', [
            'nome' => 'Teste Invasor',
            'cpf' => '12345678901',
            'setor' => 'TI',
            'telefone' => '41999998888',
            'email' => 'invasor@teste.com'
        ]);
        $this->assertTrue(in_array($response->status(), [401, 302]));

        // 3. Relatório de pontos / lista estagiários
        $response = $this->postJson('/lista-estagiarios', []);
        $this->assertTrue(in_array($response->status(), [401, 302]));

        // 4. Salvar evento / ocorrência
        $response = $this->postJson('/salvar-evento', []);
        $this->assertTrue(in_array($response->status(), [401, 302]));
    }

    /** @test */
    public function test_apenas_superadmin_pode_acessar_registro_de_novos_admins()
    {
        // 1. Visitante anônimo não acessa
        $response = $this->get(route('admin.register'));
        $response->assertRedirect(route('admin.login'));

        // 2. Admin comum (level 2) recebe 403 Forbidden
        $adminComum = Admin::factory()->create(['level' => 2]);
        $response = $this->actingAs($adminComum, 'admin')->get(route('admin.register'));
        $response->assertStatus(403);

        // 3. SuperAdmin (level 1) acessa com sucesso
        $superAdmin = Admin::factory()->create(['level' => 1]);
        $response = $this->actingAs($superAdmin, 'admin')->get(route('admin.register'));
        $response->assertStatus(200);
    }

    /** @test */
    public function test_servico_de_anonimizacao_funcoes_basicas()
    {
        $service = new AnonymizationService();

        // 1. Hashing irreversível HMAC-SHA256
        $hash1 = $service->hashSensitiveData('12345678901');
        $hash2 = $service->hashSensitiveData('12345678901');
        $this->assertEquals($hash1, $hash2);
        $this->assertEquals(64, strlen($hash1)); // SHA-256 produz 64 caracteres hex

        // 2. CPF irreversível numérico de 11 dígitos
        $cpfHash = $service->generateIrreversibleCpf('12345678901', 1);
        $this->assertEquals(11, strlen($cpfHash));
        $this->assertTrue(is_numeric($cpfHash));

        // 3. Mascaramento visual
        $this->assertEquals('***.456.789-**', $service->maskCpf('12345678901'));
        $this->assertEquals('j**o@pm.pr.gov.br', $service->maskEmail('joao@pm.pr.gov.br'));
        $this->assertEquals('(41) 9****-**88', $service->maskPhone('41988887788'));
    }

    /** @test */
    public function test_comando_artisan_ponto_anonymize_modo_hash_irreversivel()
    {
        $estagiario = Estagiario::factory()->create([
            'nm_estagiarios' => 'Carlos Real da Silva',
            'cpf' => '11122233344',
            'nr_telefone' => '41999990000',
            'nm_email' => 'carlos.real@pm.pr.gov.br',
            'ds_situacao' => true
        ]);

        $ponto = RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'atestado',
            'hr_registro' => now(),
            'ip_registro' => '187.20.10.5',
            'ds_observacao' => 'Atestado médico por gripe grave - CID J11',
            'is_abonado' => true
        ]);

        // Executa o comando de anonimização no modo hash irreversível
        $exitCode = Artisan::call('ponto:anonymize', [
            '--mode' => 'hash',
            '--force' => true
        ]);

        $this->assertEquals(0, $exitCode);

        $estagiario->refresh();
        $ponto->refresh();

        // O nome real deve ter sido apagado e substituído por pseudônimo irreversível
        $this->assertStringContainsString('Estagiário Anônimo #', $estagiario->nm_estagiarios);
        $this->assertNotEquals('11122233344', $estagiario->cpf);
        $this->assertNotEquals('carlos.real@pm.pr.gov.br', $estagiario->nm_email);
        $this->assertEquals(11, strlen($estagiario->cpf));

        // IP e observação médica sensível devem estar limpos
        $this->assertEquals('127.0.0.1', $ponto->ip_registro);
        $this->assertEquals('[Dado confidencial protegido - LGPD]', $ponto->ds_observacao);
    }

    /** @test */
    public function test_comando_artisan_ponto_anonymize_modo_faker()
    {
        $estagiario = Estagiario::factory()->create([
            'nm_estagiarios' => 'Mariana Oliveira Original',
            'cpf' => '22233344455',
            'nr_telefone' => '41988881111',
            'nm_email' => 'mariana.original@pm.pr.gov.br',
        ]);

        $exitCode = Artisan::call('ponto:anonymize', [
            '--mode' => 'faker',
            '--force' => true
        ]);

        $this->assertEquals(0, $exitCode);

        $estagiario->refresh();
        $this->assertNotEquals('Mariana Oliveira Original', $estagiario->nm_estagiarios);
        $this->assertNotEquals('22233344455', $estagiario->cpf);
        $this->assertStringContainsString('@anonimizado.local', $estagiario->nm_email);
    }
}

