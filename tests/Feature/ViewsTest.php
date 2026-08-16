<?php

namespace Tests\Feature;

use App\Domains\Admins\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste da Página Inicial (Guest)
     *
     * @return void
     */
    public function test_pagina_inicial_renderiza_corretamente_para_convidado()
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Valida CSS e estilos principais carregados
        $response->assertSee('css/vendor/bootstrap.min.css');
        $response->assertSee('css/style.css');
        
        // Valida Estrutura principal e Títulos
        $response->assertSee('PONTO DE REGISTRO');
        $response->assertSee('ESTAGIÁRIOS');
        
        // Valida Botão de Login Admin
        $response->assertSee('admin-access-btn');
        $response->assertSee('Área Admin');
        $response->assertSee(route('admin.login'));
        
        // Valida Formulário
        $response->assertSee('id="cpf"', false);
        $response->assertSee('name="cpf"', false);
        $response->assertSee('action="'.route('registrar-ponto').'"', false);
        
        // Valida Botões de Ação
        $response->assertSee('Entrada');
        $response->assertSee('Saída');
        $response->assertSee('id="registrarBtn"', false);
        $response->assertSee('REGISTRAR');
        
        // Valida Botão da Câmera
        $response->assertSee('id="btn-abrir-camera"', false);
        $response->assertSee('id="reader"', false);
    }

    /**
     * Teste da Página Inicial (Admin Autenticado)
     *
     * @return void
     */
    public function test_pagina_inicial_renderiza_corretamente_para_admin_logado()
    {
        $admin = Admin::factory()->create();
        
        $response = $this->actingAs($admin, 'admin')->get('/');

        $response->assertStatus(200);
        
        // Se estiver logado, o botão deve mudar para Painel Admin e ir para /cadastro
        $response->assertSee('Painel Admin');
        $response->assertSee(route('cadastro'));
        $response->assertDontSee('>Área Admin<'); // não deve ver o texto exato antigo
    }

    /**
     * Teste da Página de Login do Admin
     *
     * @return void
     */
    public function test_pagina_de_login_admin_renderiza_corretamente()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);

        // Estrutura
        $response->assertSee('ÁREA ADMINISTRATIVA');
        
        // Valida forms e inputs
        $response->assertSee('action="'.route('admin.login.submit').'"', false);
        $response->assertSee('name="login"', false);
        $response->assertSee('name="password"', false);
        
        // Botões e links
        $response->assertSee('ENTRAR');
    }

    /**
     * Teste de Redirecionamento da Página de Login se já logado
     *
     * @return void
     */
    public function test_login_admin_redireciona_se_logado()
    {
        $admin = Admin::factory()->create();
        
        $response = $this->actingAs($admin, 'admin')->get('/admin/login');

        // Deve redirecionar se já logado (default RedirectIfAuthenticated is / ou Home)
        $response->assertRedirect('/');
    }

    /**
     * Teste da Página do Painel Admin - Cadastro
     *
     * @return void
     */
    public function test_painel_admin_cadastro_renderiza_corretamente()
    {
        $admin = Admin::factory()->create();
        
        $response = $this->actingAs($admin, 'admin')->get('/cadastro');

        $response->assertStatus(200);

        // Sidebar e Layout
        $response->assertSee('navbar-top-menu');
        $response->assertSee('Sair');
        
        // Elementos da página
        $response->assertSee('Cadastro de Estagiários');
        
        // Botão Novo Cadastro
        $response->assertSee('Cadastrar estagiário');
        $response->assertSee('data-bs-target="#modalAdicionarEstagiario"', false);
        
        // DataTable
        $response->assertSee('tabela-estagiarios-cadastrados');
        $response->assertSee('CPF (Matrícula)');
        $response->assertSee('Setor');
        $response->assertSee('Ações');
    }

    /**
     * Teste da Página do Painel Admin - Eventos
     *
     * @return void
     */
    public function test_painel_admin_eventos_renderiza_corretamente()
    {
        $admin = Admin::factory()->create();
        
        $response = $this->actingAs($admin, 'admin')->get('/eventos');

        $response->assertStatus(200);

        // Layout
        $response->assertSee('navbar-top-menu');
        
        // Elementos da página
        $response->assertSee('Eventos e Ocorrências');
        
        // Tabela de eventos
        $response->assertSee('tabela-estagiarios-eventos');
        $response->assertSee('Nome');
        $response->assertSee('CPF (Matrícula)');
    }
}
