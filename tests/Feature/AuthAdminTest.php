<?php

namespace Tests\Feature;

use App\Domains\Admins\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa login com credenciais corretas
     *
     * @return void
     */
    public function test_admin_pode_fazer_login_com_credenciais_validas()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@teste.com',
            'password' => Hash::make('senha123'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'login' => 'admin@teste.com',
            'password' => 'senha123',
        ]);

        $this->assertAuthenticatedAs($admin, 'admin');
        
        // Verifica se redireciona para eventos ou dashboard
        // O LoginController atualmente redireciona para redirect()->intended(route('cadastro')) ou eventos
        // Se intended estiver vazio, o default_redirect_route era modificado na sessao. 
        // Apenas checamos se houve redirecionamento de sucesso.
        $response->assertStatus(302);
    }

    /**
     * Testa login com CPF correto (caso o Admin suporte login por CPF/usuário no futuro)
     * e credenciais inválidas.
     *
     * @return void
     */
    public function test_admin_nao_pode_fazer_login_com_senha_invalida()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@teste.com',
            'password' => Hash::make('senha123'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'login' => 'admin@teste.com',
            'password' => 'senha_errada',
        ]);

        $this->assertGuest('admin');
        $response->assertSessionHasErrors('login'); // o LoginController joga erro no campo 'login' ou genericamente
    }

    /**
     * Testa logout do admin
     *
     * @return void
     */
    public function test_admin_pode_fazer_logout()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $this->assertAuthenticatedAs($admin, 'admin');

        $response = $this->post(route('admin.logout'));

        $this->assertGuest('admin');
        $response->assertRedirect('/'); // redireciona para a página inicial
    }
}
