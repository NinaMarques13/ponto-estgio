<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use Carbon\Carbon;

class PontoHojeSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa registros de hoje para não duplicar nos testes
        RegistroPonto::whereDate('hr_registro', Carbon::today())->delete();
        // Pega todos os estagiários do banco
        $estagiarios = Estagiario::all();

        foreach ($estagiarios as $estagiario) {
            $this->gerarPresenca($estagiario);
        }
    }
    protected $faker;
    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }
    private function gerarPresenca($estagiario)
    {
        // 1. Define carga horária (4h ou 6h)
        $cargaHoraria = rand(0, 1) ? 4 : 6;

        // 2. Define hora de entrada aleatória (entre 07:00 e 10:00)
        // O Carbon::today() garante que seja HOJE
        $entrada = Carbon::today()->setHour(rand(7, 10))->setMinute(rand(0, 59));

        // 3. Calcula a saída baseada na entrada
        $saida = (clone $entrada)->addHours($cargaHoraria);

        // Cria registro de entrada
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => $entrada,
            'ip_registro' => $this->faker->ipv4(),
            'ds_observacao' => 'entrada'
        ]);

        // Cria registro de saída
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id, 
            'ds_motivo' => 'saida',
            'hr_registro' => $saida,
            'ip_registro' => $this->faker->ipv4(),
            'ds_observacao' => 'saida'
        ]);
    }

    /**
     * Ocorrências comentadas a pedido do usuário
     */
    /*
    private function gerarOcorrencia($estagiario)
    {
        $motivosEspeciais = ['falta', 'dispensa', 'recesso', 'folga'];

        // Escolhe um motivo aleatório da lista
        $motivo = $motivosEspeciais[array_rand($motivosEspeciais)];

        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'hr_registro' => Carbon::today()->startOfDay(),
            'ds_motivo' => $motivo,
            'ip_registro' => $this->faker->ipv4(),
            'ds_observacao' => 'motivos Especiais'
        ]);
    }
    */
}