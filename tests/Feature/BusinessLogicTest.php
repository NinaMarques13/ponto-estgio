<?php

namespace Tests\Feature;

use App\Domains\Admins\Models\Admin;
use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\Estagiarios\Services\EstagiarioService;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use App\Domains\ControleDePonto\Models\Turno;
use App\Domains\ControleDePonto\Services\PontoService;
use App\Domains\Eventos\Services\EventoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class BusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create(['level' => 1]);
        $this->actingAs($this->admin, 'admin');
    }

    /**
     * Valida cálculo exato de horas com base no turno personalizado do estagiário.
     */
    public function test_calculo_horas_com_turno_personalizado()
    {
        $estagiario = Estagiario::factory()->create();

        // Turno de 4 horas (13:00 às 17:00)
        Turno::factory()->create([
            'estagiario_id' => $estagiario->id,
            'hr_entrada' => '13:00',
            'hr_saida' => '17:00'
        ]);

        $hoje = Carbon::today();

        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => $hoje->copy()->setTime(13, 0),
            'ip_registro' => '127.0.0.1'
        ]);

        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'saida',
            'hr_registro' => $hoje->copy()->setTime(17, 0),
            'ip_registro' => '127.0.0.1'
        ]);

        $pontoService = new PontoService();
        $totalHoras = $pontoService->calculoHoras($hoje->copy()->startOfDay(), $hoje->copy()->endOfDay(), $estagiario->id);

        $this->assertEquals('04h00m', $totalHoras);
    }

    /**
     * Valida que estagiário sem turno cadastrado utiliza a carga horária padrão de 6h (360 min) em abonos.
     */
    public function test_calculo_horas_com_turno_padrao_6h_quando_sem_turno()
    {
        $estagiario = Estagiario::factory()->create();
        // Não cria registro de turno

        $hoje = Carbon::today();

        // Cria uma folga no dia
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'folga',
            'hr_registro' => $hoje->copy()->startOfDay(),
            'ip_registro' => '127.0.0.1',
            'is_abonado' => true
        ]);

        $pontoService = new PontoService();
        $totalHoras = $pontoService->calculoHoras($hoje->copy()->startOfDay(), $hoje->copy()->endOfDay(), $estagiario->id);

        $this->assertEquals('06h00m', $totalHoras);
    }

    /**
     * Valida que dia com apenas marcação de Entrada (sem Saída) contabiliza 0h até resolução manual.
     */
    public function test_calculo_horas_ignora_dias_incompletos_somente_entrada()
    {
        $estagiario = Estagiario::factory()->create();
        $hoje = Carbon::today();

        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => $hoje->copy()->setTime(8, 0),
            'ip_registro' => '127.0.0.1'
        ]);

        $pontoService = new PontoService();
        $totalHoras = $pontoService->calculoHoras($hoje->copy()->startOfDay(), $hoje->copy()->endOfDay(), $estagiario->id);

        $this->assertEquals('00h00m', $totalHoras);
    }

    /**
     * Valida que dispensa só é computada em horas caso seja expressamente abonada.
     */
    public function test_calculo_horas_abono_dia_inteiro_com_dispensa_abonada_vs_descontada()
    {
        $estagiario = Estagiario::factory()->create();

        $dia1 = Carbon::today()->startOfMonth();
        $dia2 = $dia1->copy()->addDay();

        // Dia 1: Dispensa Abonada
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'dispensa',
            'hr_registro' => $dia1->copy()->startOfDay(),
            'ip_registro' => '127.0.0.1',
            'is_abonado' => true
        ]);

        // Dia 2: Dispensa Descontada (Não abonada)
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'dispensa',
            'hr_registro' => $dia2->copy()->startOfDay(),
            'ip_registro' => '127.0.0.1',
            'is_abonado' => false
        ]);

        $pontoService = new PontoService();
        $totalHoras = $pontoService->calculoHoras($dia1->copy()->startOfDay(), $dia2->copy()->endOfDay(), $estagiario->id);

        // Apenas o Dia 1 (6h) deve ser contabilizado
        $this->assertEquals('06h00m', $totalHoras);
    }

    /**
     * Valida bloqueio com status 403 e mensagem 'Ponto fechado' caso tente bater ponto de madrugada.
     */
    public function test_registro_ponto_fora_do_horario_permitido_retorna_ponto_fechado()
    {
        $estagiario = Estagiario::factory()->create([
            'cpf' => '77788899900'
        ]);

        // Simula horário às 03:00 da madrugada (fora do horário 05:00 - 23:59)
        Carbon::setTestNow(Carbon::today()->setTime(3, 0, 0));

        try {
            $response = $this->postJson(route('registrar-ponto'), [
                'cpf' => '77788899900'
            ]);

            $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Ponto fechado'
                ]);
        } finally {
            Carbon::setTestNow(); // Restaura data/hora real do sistema
        }
    }

    /**
     * Valida reativação de estagiário previamente desativado ao tentar cadastrar com o mesmo CPF.
     */
    public function test_reativacao_de_estagiario_previamente_desativado_com_mesmo_cpf()
    {
        $estagiario = Estagiario::factory()->create([
            'cpf' => '12312312399',
            'ds_situacao' => false
        ]);

        $service = new EstagiarioService();
        $reativado = $service->criarOuAtualizar([
            'nome' => 'Estagiário Recontratado',
            'cpf' => '12312312399',
            'setor' => 'DGP / Novo Setor',
            'telefone' => '41988887777',
            'email' => 'recontratado@pm.pr.gov.br'
        ]);

        // Não cria duplicata, reativa o mesmo ID
        $this->assertEquals($estagiario->id, $reativado->id);
        $this->assertTrue((bool)$reativado->ds_situacao);
        $this->assertEquals('DGP / Novo Setor', $reativado->nm_setor);
        $this->assertEquals(1, Estagiario::where('cpf', '12312312399')->count());
    }

    /**
     * Valida geração de ocorrências em lote para múltiplos dias consecutivos.
     */
    public function test_ocorrencia_em_massa_para_multiplos_dias_consecutivos()
    {
        $estagiario = Estagiario::factory()->create();

        $inicio = Carbon::today()->startOfMonth();
        $fim = $inicio->copy()->addDays(4); // 5 dias (0, 1, 2, 3, 4)

        $eventoService = new EventoService();
        $eventoService->gerarOcorrenciaEmMassa([
            'estagiario_id' => $estagiario->id,
            'data_inicio' => $inicio->format('Y-m-d'),
            'data_fim' => $fim->format('Y-m-d'),
            'motivo' => 'recesso',
            'observacao' => 'Recesso de meio de ano'
        ], '127.0.0.1');

        $registros = RegistroPonto::where('estagiario_id', $estagiario->id)
            ->where('ds_motivo', 'recesso')
            ->get();

        $this->assertCount(5, $registros);
    }

    /**
     * Valida rejeição com 422 quando data final for anterior à data inicial no cadastro de evento.
     */
    public function test_validacao_evento_com_data_fim_anterior_a_data_inicio()
    {
        $estagiario = Estagiario::factory()->create();

        $response = $this->postJson(route('eventos.store'), [
            'estagiario_id' => $estagiario->id,
            'data_inicio' => '2026-10-10',
            'data_fim' => '2026-10-05', // Data invertida
            'motivo' => 'falta'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['data_fim']]);
    }

    /**
     * Valida os filtros do relatório de estagiários por status 'presente' vs 'andamento'.
     */
    public function test_filtro_lista_estagiarios_por_semana_e_por_status_andamento()
    {
        $hoje = Carbon::today();

        // 1. Estagiário Completo (Presente: tem Entrada e Saída)
        $estagiario1 = Estagiario::factory()->create();
        RegistroPonto::create([
            'estagiario_id' => $estagiario1->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => $hoje->copy()->setTime(8, 0),
            'ip_registro' => '127.0.0.1'
        ]);
        RegistroPonto::create([
            'estagiario_id' => $estagiario1->id,
            'ds_motivo' => 'saida',
            'hr_registro' => $hoje->copy()->setTime(14, 0),
            'ip_registro' => '127.0.0.1'
        ]);

        // 2. Estagiário Em Andamento (só tem Entrada)
        $estagiario2 = Estagiario::factory()->create();
        RegistroPonto::create([
            'estagiario_id' => $estagiario2->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => $hoje->copy()->setTime(8, 30),
            'ip_registro' => '127.0.0.1'
        ]);

        // Filtro: 'andamento'
        $responseAndamento = $this->postJson(route('lista.estagiarios'), [
            'data' => $hoje->format('Y-m-d'),
            'motivo' => 'andamento'
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $responseAndamento->assertStatus(200);
        $dadosAndamento = $responseAndamento->json('data');
        $this->assertCount(1, $dadosAndamento);
        $this->assertEquals($estagiario2->nm_estagiarios, $dadosAndamento[0]['nome']);

        // Filtro: 'presente'
        $responsePresente = $this->postJson(route('lista.estagiarios'), [
            'data' => $hoje->format('Y-m-d'),
            'motivo' => 'presente'
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $responsePresente->assertStatus(200);
        $dadosPresente = $responsePresente->json('data');
        $this->assertCount(1, $dadosPresente);
        $this->assertEquals($estagiario1->nm_estagiarios, $dadosPresente[0]['nome']);
    }
}

