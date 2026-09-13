<?php

namespace Tests\Feature;

use App\Domains\Admins\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UITest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste da Tela de Início / Quiosque de Ponto
     */
    public function test_tela_inicio_quiosque_elementos_visuais_completos()
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Identidade visual
        $response->assertSee('brasao-topo');
        $response->assertSee('dgp_transparente.png');
        $response->assertSee('PONTO DE REGISTRO');
        $response->assertSee('ESTAGIÁRIOS');

        // Formulário e input de CPF
        $response->assertSee('id="cpf"', false);
        $response->assertSee('name="cpf"', false);
        $response->assertSee('placeholder="Digite o CPF"', false);

        // Leitor de QR Code via câmera
        $response->assertSee('id="btn-abrir-camera"', false);
        $response->assertSee('lucide-camera');
        $response->assertSee('id="reader"', false);

        // Indicadores visuais de Entrada e Saída
        $response->assertSee('id="entradaTxt"', false);
        $response->assertSee('id="saidaTxt"', false);
        $response->assertSee('Primeiro');
        $response->assertSee('registro do dia: Entrada');
        $response->assertSee('Segundo registro:');
        $response->assertSee('Saída');

        // Botão de submissão
        $response->assertSee('id="registrarBtn"', false);
        $response->assertSee('REGISTRAR');

        // Acesso administrativo
        $response->assertSee('admin-access-btn');
        $response->assertSee('Área Admin');
    }

    /**
     * Teste da Tela de Login do Administrador
     */
    public function test_tela_login_admin_elementos_visuais_e_estilos()
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);

        $response->assertSee('ÁREA ADMINISTRATIVA');
        $response->assertSee('Sistema de Estagiários');
        $response->assertSee('action="' . route('admin.login.submit') . '"', false);
        $response->assertSee('name="login"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('ENTRAR');
        $response->assertSee('Cadastre-se');
        $response->assertSee(route('admin.register'));
    }

    /**
     * Teste da Tela de Cadastro de Administrador (SuperAdmin)
     */
    public function test_tela_registro_admin_elementos_visuais_para_superadmin()
    {
        $superAdmin = Admin::factory()->create(['level' => 1]);

        $response = $this->actingAs($superAdmin, 'admin')->get(route('admin.register'));

        $response->assertStatus(200);

        $response->assertSee('NOVO ADMINISTRADOR');
        $response->assertSee('action="' . route('admin.register.submit') . '"', false);
        $response->assertSee('name="nome"', false);
        $response->assertSee('name="cpf"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
        $response->assertSee('CADASTRAR');
    }

    /**
     * Teste do Painel de Cadastro de Estagiários e Modais
     */
    public function test_painel_cadastro_tabela_e_modais_de_estagiarios()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('cadastro'));

        $response->assertStatus(200);

        // Cabeçalho e botões
        $response->assertSee('Cadastro de Estagiários');
        $response->assertSee('Cadastrar estagiário');
        $response->assertSee('data-bs-target="#modalAdicionarEstagiario"', false);

        // Tabela DataTables
        $response->assertSee('id="tabela-estagiarios-cadastrados"', false);
        $response->assertSee('Nome');
        $response->assertSee('CPF (Matrícula)');
        $response->assertSee('Setor');
        $response->assertSee('Telefone');
        $response->assertSee('E-mail');
        $response->assertSee('Ações');

        // Modais de Criação e Edição
        $response->assertSee('id="modalAdicionarEstagiario"', false);
        $response->assertSee('id="modalEditarEstagiario"', false);
        $response->assertSee('id="formAdicionarEstagiario"', false);

        // Modal de QR Code com botão de impressão
        $response->assertSee('id="qrModalCadastro"', false);
        $response->assertSee('id="qrcodeCadastro"', false);
        $response->assertSee('Imprimir QR Code');
        $response->assertSee('window.print()', false);
    }

    /**
     * Teste do Painel de Ocorrências e Eventos
     */
    public function test_painel_eventos_tabela_e_modais_de_ocorrencias()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('eventos'));

        $response->assertStatus(200);

        $response->assertSee('Eventos e Ocorrências');
        $response->assertSee('id="tabela-estagiarios-eventos"', false);
        $response->assertSee('id="modalAdicionarEvento"', false);
        $response->assertSee('id="modalListarEventos"', false);
        $response->assertSee('id="add-evento-secao-conflitos"', false);
    }

    /**
     * Teste do Painel de Exportação, KPIs e Relatório
     */
    public function test_painel_export_cards_kpis_filtros_e_tabela_relatorio()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('export'));

        $response->assertStatus(200);

        // Filtros de busca
        $response->assertSee('id="data-completa"', false);
        $response->assertSee('id="data-mes"', false);
        $response->assertSee('id="data-semana-inicio"', false);
        $response->assertSee('id="data-semana-fim"', false);
        $response->assertSee('id="data-ano"', false);
        $response->assertSee('id="filtro-motivo"', false);

        // Cards de KPIs / Totalizadores
        $response->assertSee('id="contador-presentes"', false);
        $response->assertSee('id="registros-dia"', false);
        $response->assertSee('id="recesso-dia"', false);
        $response->assertSee('id="atestados-dia"', false);
        $response->assertSee('id="folgas-dia"', false);
        $response->assertSee('id="dispensas-dia"', false);
        $response->assertSee('id="faltas-dia"', false);

        // Tabela de registros com colunas completas
        $response->assertSee('id="myTable"', false);
        $response->assertSee('Hora Entrada');
        $response->assertSee('Hora Saída');
        $response->assertSee('Total Horas');
        $response->assertSee('Matrícula (CPF)');
        $response->assertSee('Observação');
    }

    /**
     * Teste do Layout Padrão e Navegação Superior
     */
    public function test_layout_menu_superior_e_links_de_navegacao()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('cadastro'));

        $response->assertStatus(200);

        $response->assertSee('navbar-top-menu');
        $response->assertSee('Sistema de Estagiários');
        $response->assertSee(route('eventos'));
        $response->assertSee(route('cadastro'));
        $response->assertSee(route('export'));

        // Botão de Sair com formulário de logout
        $response->assertSee('action="' . route('admin.logout') . '"', false);
        $response->assertSee('Sair');
        $response->assertSee('Ir para Ponto de Registro');
    }

    /**
     * Teste de Renderização das Páginas de Erro Customizadas
     */
    public function test_paginas_de_erro_customizadas()
    {
        // 403 Forbidden
        $view403 = view('errors.403')->render();
        $this->assertStringContainsString('403', $view403);
        $this->assertStringContainsString('Acesso Negado', $view403);

        // 404 Not Found
        $view404 = view('errors.404')->render();
        $this->assertStringContainsString('404', $view404);
        $this->assertStringContainsString('Página Não Encontrada', $view404);

        // 500 Internal Error
        $view500 = view('errors.500')->render();
        $this->assertStringContainsString('500', $view500);
        $this->assertStringContainsString('Erro do Servidor', $view500);
    }
}
