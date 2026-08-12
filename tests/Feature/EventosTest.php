<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Estagiario;
use App\Models\RegistroPonto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventosTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // O gerenciamento de eventos requer um admin logado.
        $this->admin = Admin::factory()->create();
    }

    /**
     * Testa o método getEventosEstagiario
     *
     * @return void
     */
    public function test_pode_recuperar_eventos_de_um_estagiario_especifico()
    {
        $estagiario = Estagiario::factory()->create();
        
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'falta',
            'hr_registro' => now(),
            'ip_registro' => '127.0.0.1'
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get("/estagiarios/{$estagiario->id}/listar-eventos");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'estagiario' => $estagiario->nm_estagiarios
        ]);
        
        $this->assertCount(1, $response->json('eventos'));
        $this->assertEquals('falta', $response->json('eventos.0.tipo_bruto'));
    }

    /**
     * Testa o método verificarPeriodo
     *
     * @return void
     */
    public function test_pode_verificar_registros_em_um_determinado_periodo()
    {
        $estagiario = Estagiario::factory()->create();
        
        $data = now();
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'dispensa',
            'hr_registro' => $data,
            'ip_registro' => '127.0.0.1'
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get("/estagiarios/{$estagiario->id}/verificar-periodo?inicio={$data->format('Y-m-d')}&fim={$data->format('Y-m-d')}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Deve retornar 1 registro que coincide com a dispensa criada
        $this->assertCount(1, $response->json('registros'));
    }

    /**
     * Testa a validação do método storeEvento
     * Tentar salvar correção de ponto sem informar horas deve falhar.
     *
     * @return void
     */
    public function test_falha_ao_criar_evento_correcao_sem_informar_hora()
    {
        $estagiario = Estagiario::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')->post('/salvar-evento', [
            'estagiario_id' => $estagiario->id,
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->format('Y-m-d'),
            'motivo' => 'correcao'
            // omitindo hora_entrada e hora_saida intencionalmente
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJson(['success' => false]);
    }

    /**
     * Testa o método destroyEvento
     *
     * @return void
     */
    public function test_pode_excluir_evento_isolado()
    {
        $estagiario = Estagiario::factory()->create();
        
        $evento = RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'recesso',
            'hr_registro' => now(),
            'ip_registro' => '127.0.0.1'
        ]);

        $response = $this->actingAs($this->admin, 'admin')->delete("/excluir-evento/{$evento->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verifica no banco (se está usando softdeletes ou deletes definitivos)
        // No caso do sistema, RegistroPonto usa SoftDeletes
        $this->assertSoftDeleted('registro_ponto', [
            'id' => $evento->id
        ]);
    }

    /**
     * Testa a rota da datatable de eventos
     *
     * @return void
     */
    public function test_datatable_lista_estagiarios_eventos_funciona()
    {
        $estagiario = Estagiario::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')->get('/estagiarios-eventos');

        $response->assertStatus(200);
        
        // Datatable structure
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data'
        ]);
    }
}
