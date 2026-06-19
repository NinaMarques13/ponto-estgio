<?php

namespace Tests\Feature;

use App\Models\Estagiario;
use App\Models\RegistroPonto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class EstagiariosTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * ====================================
     * TESTES DE CADASTRO DE ESTAGIÁRIOS
     * ====================================
     */

    /** @test */
    public function test_pode_criar_novo_estagiario()
    {
        $dados = [
            'nm_estagiarios' => 'João Silva',
            'nr_matricula' => '12345678901',
            'nm_setor' => 'TI',
            'nr_telefone' => '11987654321',
            'nm_email' => 'joao@example.com',
        ];

        $response = $this->postJson('/cadastrar-estagiario', $dados);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'dados' => [
                    'id',
                    'nm_estagiarios',
                    'nr_matricula',
                    'nm_setor'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Estagiario cadastrado com sucesso!'
            ]);

        $this->assertDatabaseHas('estagiarios', [
            'nm_estagiarios' => 'João Silva',
            'nr_matricula' => '12345678901',
            'nm_setor' => 'TI',
        ]);
    }

    /** @test */
    public function test_validacao_campos_obrigatorios_cadastro()
    {
        $dados = [
            'nm_estagiarios' => '',
            'nr_matricula' => '',
            'nm_setor' => '',
            'nr_telefone' => '',
            'nm_email' => '',
        ];

        $response = $this->postJson('/cadastrar-estagiario', $dados);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Erro de validação!'
            ]);
    }

    /** @test */
    public function test_validacao_email_invalido()
    {
        $dados = [
            'nm_estagiarios' => 'João Silva',
            'nr_matricula' => '12345678901',
            'nm_setor' => 'TI',
            'nr_telefone' => '11987654321',
            'nm_email' => 'email-invalido',
        ];

        $response = $this->postJson('/cadastrar-estagiario', $dados);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function test_validacao_matricula_duplicada()
    {
        $estagiario1 = Estagiario::factory()->create([
            'nr_matricula' => '12345678901'
        ]);

        $dados = [
            'nm_estagiarios' => 'Maria Silva',
            'nr_matricula' => '12345678901',
            'nm_setor' => 'RH',
            'nr_telefone' => '11987654322',
            'nm_email' => 'maria@example.com',
        ];

        $response = $this->postJson('/cadastrar-estagiario', $dados);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function test_validacao_telefone_duplicado()
    {
        Estagiario::factory()->create([
            'nr_telefone' => '11987654321'
        ]);

        $dados = [
            'nm_estagiarios' => 'João Silva',
            'nr_matricula' => '12345678901',
            'nm_setor' => 'TI',
            'nr_telefone' => '11987654321',
            'nm_email' => 'joao@example.com',
        ];

        $response = $this->postJson('/cadastrar-estagiario', $dados);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function test_validacao_email_duplicado()
    {
        Estagiario::factory()->create([
            'nm_email' => 'joao@example.com'
        ]);

        $dados = [
            'nm_estagiarios' => 'João Silva',
            'nr_matricula' => '12345678901',
            'nm_setor' => 'TI',
            'nr_telefone' => '11987654321',
            'nm_email' => 'joao@example.com',
        ];

        $response = $this->postJson('/cadastrar-estagiario', $dados);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function test_atualizar_cadastro_existente()
    {
        $estagiario = Estagiario::factory()->create();

        $dadosAtualizados = [
            'nome' => 'João Atualizado',
            'cpf' => $estagiario->nr_matricula,
            'setor' => 'RH',
            'telefone' => $estagiario->nr_telefone,
            'email' => $estagiario->nm_email,
        ];

        $response = $this->putJson("/atualizar-cadastro/{$estagiario->id}", $dadosAtualizados);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Estagiario atualizado com sucesso!'
            ]);

        $estagiario->refresh();
        $this->assertEquals('João Atualizado', $estagiario->nm_estagiarios);
        $this->assertEquals('RH', $estagiario->nm_setor);
    }

    /** @test */
    public function test_desativar_estagiario()
    {
        $estagiario = Estagiario::factory()->create([
            'ds_situacao' => true
        ]);

        $response = $this->putJson("/desativar-estagiario/{$estagiario->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Estagiário excluído com sucesso!']);

        $estagiario->refresh();
        $this->assertFalse($estagiario->ds_situacao);
    }

    /**
     * ====================================
     * TESTES DE LISTAGEM/EXPORTAÇÃO
     * ====================================
     */

    /** @test */
    public function test_listar_estagiarios_cadastrados()
    {
        Estagiario::factory()->count(3)->create([
            'ds_situacao' => true
        ]);

        $response = $this->getJson('/estagiarios-cadastrados');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'recordsTotal',
                'recordsFiltered'
            ]);
    }

    /** @test */
    public function test_listar_registros_por_data()
    {
        $estagiario = Estagiario::factory()->create();
        $hoje = Carbon::today();

        // Criar registros de entrada e saída
        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'hr_registro' => $hoje->copy()->setTime(8, 0, 0),
            'ds_motivo' => 'entrada',
        ]);

        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'hr_registro' => $hoje->copy()->setTime(18, 0, 0),
            'ds_motivo' => 'saida',
        ]);

        $response = $this->postJson('/lista-estagiarios', [
            'data' => $hoje->format('Y-m-d')
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_listar_registros_por_mes()
    {
        $estagiario = Estagiario::factory()->create();
        $mes = Carbon::now()->format('Y-m');

        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
        ]);

        $response = $this->postJson('/lista-estagiarios', [
            'mes' => $mes
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_listar_registros_por_ano()
    {
        $estagiario = Estagiario::factory()->create();
        $ano = Carbon::now()->format('Y');

        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
        ]);

        $response = $this->postJson('/lista-estagiarios', [
            'ano' => $ano
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_filtrar_por_estagiario_especifico()
    {
        $estagiario1 = Estagiario::factory()->create();
        $estagiario2 = Estagiario::factory()->create();

        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario1->id,
            'ds_motivo' => 'entrada',
        ]);

        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario2->id,
            'ds_motivo' => 'entrada',
        ]);

        $response = $this->postJson('/lista-estagiarios', [
            'estagiario_id' => $estagiario1->id
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_filtrar_por_motivo_presente()
    {
        $estagiario = Estagiario::factory()->create();
        $hoje = Carbon::today();

        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'hr_registro' => $hoje->copy()->setTime(8, 0, 0),
            'ds_motivo' => 'entrada',
        ]);

        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'hr_registro' => $hoje->copy()->setTime(18, 0, 0),
            'ds_motivo' => 'saida',
        ]);

        $response = $this->postJson('/lista-estagiarios', [
            'motivo' => 'presente'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_filtrar_por_motivo_falta()
    {
        $estagiario = Estagiario::factory()->create();

        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'falta',
        ]);

        $response = $this->postJson('/lista-estagiarios', [
            'motivo' => 'falta'
        ]);

        $response->assertStatus(200);
    }

    /**
     * ====================================
     * TESTES DE REGISTRO DE PONTO
     * ====================================
     */

    /** @test */
    public function test_registrar_entrada_ponto()
    {
        $estagiario = Estagiario::factory()->create([
            'nr_matricula' => '12345678901'
        ]);

        $response = $this->postJson('/registrar-ponto', [
            'cpf' => '12345678901'
        ]);

        $response->assertStatus(200)
            ->assertSessionHas('sucesso', 'Ponto de entrada registrado com sucesso!');

        $this->assertDatabaseHas('registro_ponto', [
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
        ]);
    }

    /** @test */
    public function test_registrar_saida_apos_entrada()
    {
        $estagiario = Estagiario::factory()->create([
            'nr_matricula' => '12345678901'
        ]);

        // Registra entrada
        $this->postJson('/registrar-ponto', [
            'cpf' => '12345678901'
        ]);

        // Registra saída
        $response = $this->postJson('/registrar-ponto', [
            'cpf' => '12345678901'
        ]);

        $response->assertStatus(200)
            ->assertSessionHas('sucesso', 'Ponto de saida registrado com sucesso!');

        $registros = RegistroPonto::where('estagiario_id', $estagiario->id)->get();
        $this->assertCount(2, $registros);
        $this->assertEquals('entrada', $registros[0]->ds_motivo);
        $this->assertEquals('saida', $registros[1]->ds_motivo);
    }

    /** @test */
    public function test_erro_estagiario_nao_encontrado()
    {
        $response = $this->postJson('/registrar-ponto', [
            'cpf' => '99999999999'
        ]);

        $response->assertStatus(302)
            ->assertSessionHas('erro', 'Estagiário não encontrado com o CPF: 99999999999');
    }

    /**
     * ====================================
     * TESTES DE ATUALIZAÇÃO DE PONTO
     * ====================================
     */

    /** @test */
    public function test_atualizar_horario_entrada()
    {
        $estagiario = Estagiario::factory()->create();
        $hoje = Carbon::today();

        $registro = RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'hr_registro' => $hoje->copy()->setTime(8, 0, 0),
            'ds_motivo' => 'entrada',
        ]);

        $response = $this->putJson("/atualizar-estagiarios/{$estagiario->id}", [
            'data' => $hoje->format('Y-m-d'),
            'entrada' => '09:00',
            'saida' => '',
            'matricula' => $estagiario->nr_matricula,
            'nome' => $estagiario->nm_estagiarios,
            'motivo' => 'entrada',
            'setor' => $estagiario->nm_setor,
            'observacao' => 'Ajuste de horário'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Estagiário atualizado com sucesso'
            ]);

        $registro->refresh();
        $this->assertEquals('09:00', $registro->hr_registro->format('H:i'));
    }

    /** @test */
    public function test_atualizar_motivo_registro()
    {
        $estagiario = Estagiario::factory()->create();
        $hoje = Carbon::today();

        $registro = RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'hr_registro' => $hoje->copy()->setTime(8, 0, 0),
            'ds_motivo' => 'entrada',
            'ds_observacao' => 'entrada',
        ]);

        $response = $this->putJson("/atualizar-estagiarios/{$estagiario->id}", [
            'data' => $hoje->format('Y-m-d'),
            'entrada' => '',
            'saida' => '',
            'matricula' => $estagiario->nr_matricula,
            'nome' => $estagiario->nm_estagiarios,
            'motivo' => 'falta',
            'setor' => $estagiario->nm_setor,
            'observacao' => 'Falta justificada'
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $registro->refresh();
        $this->assertEquals('falta', $registro->ds_motivo);
        $this->assertEquals('Falta justificada', $registro->ds_observacao);
    }

    /**
     * ====================================
     * TESTES DE PESQUISA
     * ====================================
     */
    /**
     * ====================================
     * TESTES DE QR CODE
     * ====================================
     */

    /** @test */
    public function test_processar_qrcode_valido()
    {
        $estagiario = Estagiario::factory()->create([
            'nr_matricula' => '12345678901'
        ]);

        $response = $this->postJson('/processar-qrcode', [
            'cpf' => '12345678901'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data'])
            ->assertJson(['status' => 'sucesso']);
    }

    /** @test */
    public function test_processar_qrcode_invalido()
    {
        $response = $this->postJson('/processar-qrcode', [
            'cpf' => '99999999999'
        ]);

        $response->assertStatus(404)
            ->assertJson(['status' => 'erro']);
    }

    /**
     * ====================================
     * TESTES DE FLUXO COMPLETO
     * ====================================
     */

    /** @test */
    public function test_fluxo_completo_cadastro_e_registro()
    {
        // 1. Cadastrar novo estagiário
        $dadosCadastro = [
            'nm_estagiarios' => 'Carlos Silva',
            'nr_matricula' => '98765432101',
            'nm_setor' => 'Financeiro',
            'nr_telefone' => '11987654321',
            'nm_email' => 'carlos@example.com',
        ];

        $responseCadastro = $this->postJson('/cadastrar-estagiario', $dadosCadastro);
        $responseCadastro->assertStatus(200)->assertJson(['success' => true]);

        // 2. Verificar se o estagiário foi criado
        $this->assertDatabaseHas('estagiarios', [
            'nr_matricula' => '98765432101'
        ]);

        // 3. Registrar entrada
        $responseEntrada = $this->postJson('/registrar-ponto', [
            'cpf' => '98765432101'
        ]);
        $responseEntrada->assertStatus(200)->assertSessionHas('sucesso');

        // 4. Registrar saída
        $responseSaida = $this->postJson('/registrar-ponto', [
            'cpf' => '98765432101'
        ]);
        $responseSaida->assertStatus(200)->assertSessionHas('sucesso');

        // 5. Listar registros do dia
        $responseListar = $this->postJson('/lista-estagiarios', [
            'data' => Carbon::today()->format('Y-m-d')
        ]);
        $responseListar->assertStatus(200);

        // 6. Verificar registros no banco
        $registros = RegistroPonto::where('estagiario_id', 1)->get();
        $this->assertCount(2, $registros);
    }

    /** @test */
    public function test_fluxo_completo_com_atualizacoes()
    {
        // 1. Criar estagiário
        $estagiario = Estagiario::factory()->create([
            'nm_estagiarios' => 'Ana Costa',
            'nr_matricula' => '55555555555',
        ]);

        // 2. Registrar ponto
        RegistroPonto::factory()->create([
            'estagiario_id' => $estagiario->id,
            'hr_registro' => Carbon::today()->setTime(8, 0),
            'ds_motivo' => 'entrada',
        ]);

        // 3. Atualizar dados do estagiário
        $responseUpdate = $this->putJson("/atualizar-cadastro/{$estagiario->id}", [
            'nome' => 'Ana Costa Silva',
            'cpf' => '55555555555',
            'setor' => 'Gestão',
            'telefone' => '11999999999',
            'email' => 'ana@example.com',
        ]);
        $responseUpdate->assertStatus(200);

        // 4. Atualizar registro de ponto
        $responsePonto = $this->putJson("/atualizar-estagiarios/{$estagiario->id}", [
            'data' => Carbon::today()->format('Y-m-d'),
            'entrada' => '07:30',
            'saida' => '17:30',
            'matricula' => '55555555555',
            'nome' => 'Ana Costa Silva',
            'motivo' => 'entrada',
            'setor' => 'Gestão',
            'observacao' => 'Dia normal'
        ]);
        $responsePonto->assertStatus(200);

        // 5. Listar e verificar
        $estagiario->refresh();
        $this->assertEquals('Ana Costa Silva', $estagiario->nm_estagiarios);
    }
}
