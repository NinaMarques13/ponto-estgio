<?php

namespace Tests\Feature;

use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
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
            'nome' => 'João Silva',
            'cpf' => '12345678901',
            'setor' => 'TI',
            'telefone' => '11987654321',
            'email' => 'joao@example.com',
        ];

        $response = $this->postJson('/cadastrar-estagiario', $dados);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'dados' => [
                    'id',
                    'nm_estagiarios',
                    'cpf',
                    'nm_setor'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Estagiario cadastrado com sucesso!'
            ]);

        $this->assertDatabaseHas('estagiarios', [
            'nm_estagiarios' => 'João Silva',
            'cpf' => '12345678901',
            'nm_setor' => 'TI',
        ]);
    }

    /** @test */
    public function test_validacao_campos_obrigatorios_cadastro()
    {
        $dados = [
            'nome' => '',
            'cpf' => '',
            'setor' => '',
            'telefone' => '',
            'email' => '',
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
            'nome' => 'João Silva',
            'cpf' => '12345678901',
            'setor' => 'TI',
            'telefone' => '11987654321',
            'email' => 'email-invalido',
        ];

        $response = $this->postJson('/cadastrar-estagiario', $dados);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function test_validacao_matricula_duplicada()
    {
        $estagiario1 = Estagiario::factory()->create([
            'cpf' => '12345678901'
        ]);

        $dados = [
            'nome' => 'Maria Silva',
            'cpf' => '12345678901',
            'setor' => 'RH',
            'telefone' => '11987654322',
            'email' => 'maria@example.com',
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
            'nome' => 'João Silva',
            'cpf' => '12345678901',
            'setor' => 'TI',
            'telefone' => '11987654321',
            'email' => 'joao@example.com',
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
            'nome' => 'João Silva',
            'cpf' => '12345678901',
            'setor' => 'TI',
            'telefone' => '11987654321',
            'email' => 'joao@example.com',
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
            'cpf' => $estagiario->cpf,
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
            'cpf' => '12345678901'
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
            'cpf' => '12345678901'
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
            'cpf' => $estagiario->cpf,
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
            'cpf' => $estagiario->cpf,
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
            'cpf' => '12345678901'
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
            'nome' => 'Carlos Silva',
            'cpf' => '98765432101',
            'setor' => 'Financeiro',
            'telefone' => '11987654321',
            'email' => 'carlos@example.com',
        ];

        $responseCadastro = $this->postJson('/cadastrar-estagiario', $dadosCadastro);
        $responseCadastro->assertStatus(200)->assertJson(['success' => true]);

        // 2. Verificar se o estagiário foi criado
        $this->assertDatabaseHas('estagiarios', [
            'cpf' => '98765432101'
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
            'cpf' => '55555555555',
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
            'cpf' => '55555555555',
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

    /** @test */
    public function test_fluxo_gerar_evento_atestado_abonado()
    {
        $estagiario = Estagiario::factory()->create();

        // 1. Salvar evento atestado abonado
        $response = $this->postJson('/salvar-evento', [
            'estagiario_id' => $estagiario->id,
            'data_inicio' => Carbon::today()->format('Y-m-d'),
            'data_fim' => Carbon::today()->format('Y-m-d'),
            'motivo' => 'atestado',
            'is_abonado' => true,
            'observacao' => 'Atestado médico de teste'
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('registro_ponto', [
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'atestado',
            'is_abonado' => true
        ]);
    }

    /** @test */
    public function test_fluxo_gerar_evento_correcao_dia()
    {
        $estagiario = Estagiario::factory()->create();

        // 1. Salvar evento correção dia (deve gerar entrada e saída)
        $response = $this->postJson('/salvar-evento', [
            'estagiario_id' => $estagiario->id,
            'data_inicio' => Carbon::today()->format('Y-m-d'),
            'data_fim' => Carbon::today()->format('Y-m-d'),
            'motivo' => 'correcao',
            'hora_entrada' => '08:00',
            'hora_saida' => '12:00',
            'observacao' => 'Correção de teste'
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('registro_ponto', [
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => Carbon::today()->setTime(8, 0)->format('Y-m-d H:i:s')
        ]);

        $this->assertDatabaseHas('registro_ponto', [
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'saida',
            'hr_registro' => Carbon::today()->setTime(12, 0)->format('Y-m-d H:i:s')
        ]);
    }

    /** @test */
    public function test_calculo_horas_com_recesso_e_atestado_abonado()
    {
        $estagiario = Estagiario::factory()->create();
        
        // Cria um turno de 6 horas (08:00 a 14:00)
        \App\Domains\ControleDePonto\Models\Turno::factory()->create([
            'estagiario_id' => $estagiario->id,
            'hr_entrada' => '08:00',
            'hr_saida' => '14:00',
        ]);

        // 1. Cria um recesso no primeiro dia
        $dia1 = Carbon::today()->startOfMonth();
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'recesso',
            'hr_registro' => $dia1->copy()->startOfDay(),
            'ip_registro' => '127.0.0.1',
            'is_abonado' => true,
        ]);

        // 2. Cria um atestado abonado no segundo dia
        $dia2 = $dia1->copy()->addDay();
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'atestado',
            'hr_registro' => $dia2->copy()->startOfDay(),
            'ip_registro' => '127.0.0.1',
            'is_abonado' => true,
        ]);

        // 3. Cria um atestado NÃO abonado (descontado) no terceiro dia
        $dia3 = $dia1->copy()->addDays(2);
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'atestado',
            'hr_registro' => $dia3->copy()->startOfDay(),
            'ip_registro' => '127.0.0.1',
            'is_abonado' => false,
        ]);

        // 4. Executa a listagem e verifica se o total de horas é 12h00m (6h do recesso + 6h do atestado abonado)
        $response = $this->postJson('/lista-estagiarios', [
            'mes' => $dia1->format('Y-m')
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $dados = $response->json('data');

        // Encontra a linha referente ao estagiário e verifica a coluna total_horas
        $row = collect($dados)->firstWhere('estagiario_id', $estagiario->id);
        $this->assertNotNull($row);
        $this->assertEquals('12h00m', $row['total_horas']);
    }

    /** @test */
    public function test_correcao_dia_apenas_entrada_preserva_saida()
    {
        $estagiario = Estagiario::factory()->create();
        $hoje = Carbon::today();

        // Cria registros pré-existentes de entrada e saída
        $entradaOriginal = RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => $hoje->copy()->setTime(8, 0, 0),
            'ip_registro' => '127.0.0.1'
        ]);

        $saidaOriginal = RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'saida',
            'hr_registro' => $hoje->copy()->setTime(12, 0, 0),
            'ip_registro' => '127.0.0.1'
        ]);

        // Executa correção de dia apenas para a entrada (altera para 07:30)
        $response = $this->postJson('/salvar-evento', [
            'estagiario_id' => $estagiario->id,
            'data_inicio' => $hoje->format('Y-m-d'),
            'data_fim' => $hoje->format('Y-m-d'),
            'motivo' => 'correcao',
            'hora_entrada' => '07:30',
            'hora_saida' => null,
            'observacao' => 'Corrigindo entrada'
        ]);

        $response->assertStatus(200);

        // Verifica que a entrada foi atualizada
        $this->assertDatabaseHas('registro_ponto', [
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => $hoje->copy()->setTime(7, 30, 0)->format('Y-m-d H:i:s')
        ]);

        // E a entrada original de 08:00 foi removida logicamente
        $this->assertSoftDeleted('registro_ponto', [
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => $hoje->copy()->setTime(8, 0, 0)->format('Y-m-d H:i:s')
        ]);

        // E a saída original (12:00) continua existindo intacta
        $this->assertDatabaseHas('registro_ponto', [
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'saida',
            'hr_registro' => $hoje->copy()->setTime(12, 0, 0)->format('Y-m-d H:i:s')
        ]);
    }

    /** @test */
    public function test_exclusao_eventos_lote()
    {
        $estagiario = Estagiario::factory()->create();
        $hoje = Carbon::today();

        // 1. Cria 3 registros de recesso
        $r1 = RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'recesso',
            'hr_registro' => $hoje->copy()->startOfDay(),
            'ip_registro' => '127.0.0.1'
        ]);

        $r2 = RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'recesso',
            'hr_registro' => $hoje->copy()->addDay()->startOfDay(),
            'ip_registro' => '127.0.0.1'
        ]);

        $r3 = RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'recesso',
            'hr_registro' => $hoje->copy()->addDays(2)->startOfDay(),
            'ip_registro' => '127.0.0.1'
        ]);

        // 2. Chama a rota de exclusão em lote para r1 e r2
        $response = $this->postJson('/excluir-eventos-lote', [
            'ids' => [$r1->id, $r2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // 3. Verifica que r1 e r2 foram deletados logicamente, mas r3 continua ativo lá
        $this->assertSoftDeleted('registro_ponto', ['id' => $r1->id]);
        $this->assertSoftDeleted('registro_ponto', ['id' => $r2->id]);
        $this->assertDatabaseHas('registro_ponto', ['id' => $r3->id]);
    }
}
