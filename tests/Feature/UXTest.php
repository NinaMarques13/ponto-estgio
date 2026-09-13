<?php

namespace Tests\Feature;

use App\Domains\Admins\Models\Admin;
use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tests\TestCase;

class UXTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valida que o quiosque aceita CPF digitado tanto com pontuação quanto números puros.
     */
    public function test_quiosque_aceita_cpf_com_mascara_ou_sem_mascara()
    {
        $estagiario = Estagiario::factory()->create([
            'cpf' => '12345678901'
        ]);

        // 1. Envio com máscara (ex: 123.456.789-01)
        $response1 = $this->post(route('registrar-ponto'), [
            'cpf' => '123.456.789-01'
        ]);
        $response1->assertSessionHas('sucesso', 'Ponto de entrada registrado com sucesso!');

        // 2. Envio sem máscara (ex: 12345678901)
        $response2 = $this->post(route('registrar-ponto'), [
            'cpf' => '12345678901'
        ]);
        $response2->assertSessionHas('sucesso', 'Ponto de saida registrado com sucesso!');

        $this->assertCount(2, RegistroPonto::where('estagiario_id', $estagiario->id)->get());
    }

    /**
     * Valida os feedbacks visuais ao bater ponto alternando entre Entrada e Saída no mesmo dia.
     */
    public function test_quiosque_feedback_ao_registrar_entrada_e_saida()
    {
        $estagiario = Estagiario::factory()->create([
            'cpf' => '98765432100'
        ]);

        // 1º registro do dia -> Entrada
        $response1 = $this->postJson(route('registrar-ponto'), [
            'cpf' => '98765432100'
        ]);
        $response1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ponto de entrada registrado com sucesso!'
            ]);

        // 2º registro do dia -> Saída
        $response2 = $this->postJson(route('registrar-ponto'), [
            'cpf' => '98765432100'
        ]);
        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ponto de saida registrado com sucesso!'
            ]);
    }

    /**
     * Valida feedback neutro e seguro quando um CPF não existente é digitado no quiosque.
     */
    public function test_quiosque_feedback_neutro_quando_cpf_nao_encontrado()
    {
        $response = $this->post(route('registrar-ponto'), [
            'cpf' => '000.111.222-33'
        ]);

        $response->assertSessionHas('erro', 'Matrícula/CPF não encontrado no sistema.');
    }

    /**
     * Valida bloqueio automático com HTTP 429 caso cliques repetitivos excedam o rate limit.
     */
    public function test_bloqueio_por_rate_limiting_retorna_429_apos_limite_excedido()
    {
        $estagiario = Estagiario::factory()->create([
            'cpf' => '11122233344'
        ]);

        // Executa 30 requisições (limite permitido no minuto)
        for ($i = 0; $i < 30; $i++) {
            $this->postJson(route('registrar-ponto'), ['cpf' => '11122233344']);
        }

        // A 31ª requisição deve retornar 429 Too Many Requests
        $response = $this->postJson(route('registrar-ponto'), ['cpf' => '11122233344']);
        $response->assertStatus(429);
    }

    /**
     * Valida proteção contra força bruta no Login de Administrador (limite de 5 tentativas).
     */
    public function test_rate_limiting_login_admin_bloqueia_forca_bruta()
    {
        // 5 tentativas erradas
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('admin.login.submit'), [
                'login' => 'invasor@pm.pr.gov.br',
                'password' => 'senha_errada'
            ]);
            $response->assertSessionHasErrors('login');
        }

        // A 6ª tentativa consecutiva é bloqueada pelo throttle
        $responseBloqueada = $this->post(route('admin.login.submit'), [
            'login' => 'invasor@pm.pr.gov.br',
            'password' => 'senha_errada'
        ]);
        $responseBloqueada->assertStatus(429);
    }

    /**
     * Valida que erro de login preserva o campo login preenchido para evitar retrabalho do usuário.
     */
    public function test_login_com_credenciais_invalidas_preserva_input_usuario()
    {
        $response = $this->post(route('admin.login.submit'), [
            'login' => 'admin_digitado@pm.pr.gov.br',
            'password' => 'senha_incorreta'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('login');
        $this->assertEquals('admin_digitado@pm.pr.gov.br', session('_old_input.login'));
    }

    /**
     * Valida fluxo limpo de logout com destruição de sessão e retorno à página inicial.
     */
    public function test_logout_invalida_sessao_e_redireciona_com_sucesso()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $this->assertAuthenticatedAs($admin, 'admin');

        $response = $this->post(route('admin.logout'));

        $this->assertGuest('admin');
        $response->assertRedirect('/');
    }

    /**
     * Valida leitura de QR Code aceitando texto numérico puro ou com pontuação.
     */
    public function test_processamento_qrcode_com_cpf_formatado_e_nao_formatado()
    {
        $estagiario = Estagiario::factory()->create([
            'nm_estagiarios' => 'Roberto Carlos',
            'cpf' => '55566677788'
        ]);

        // 1. Processa QR Code com pontuação
        $response1 = $this->postJson(route('processar.qrcode'), [
            'cpf' => '555.666.777-88'
        ]);
        $response1->assertStatus(200)
            ->assertJson([
                'status' => 'sucesso',
                'data' => 'Estagiário: Roberto Carlos | Matrícula: 55566677788'
            ]);

        // 2. Processa QR Code numérico limpo
        $response2 = $this->postJson(route('processar.qrcode'), [
            'cpf' => '55566677788'
        ]);
        $response2->assertStatus(200)
            ->assertJson([
                'status' => 'sucesso'
            ]);

        // 3. QR Code de CPF não cadastrado
        $response3 = $this->postJson(route('processar.qrcode'), [
            'cpf' => '00000000000'
        ]);
        $response3->assertStatus(404)
            ->assertJson([
                'status' => 'erro',
                'mensagem' => 'Estagiário não encontrado com a matrícula informada.'
            ]);
    }
}

