<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estagiario;
use App\Models\RegistroPonto; // Confirme se o caminho da Model está certo
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

            // 🎲 Sorteia o cenário do dia para este estagiário
            // 1 a 70: Veio trabalhar (70% de chance)
            // 71 a 100: Ocorrência especial (30% de chance)
            $sorteio = rand(1, 100);

            if ($sorteio <= 70) {
                $this->gerarPresenca($estagiario);
            } else {
                $this->gerarOcorrencia($estagiario);
            }
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

        // Cria registro de eNTRADA
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => $entrada,
            'ip_registro' => $this->faker->ipv4(),
            'ds_observacao' => 'entrada'
        ]);
        $saida = (clone $entrada)->addHours($cargaHoraria);
        $agora = Carbon::now();

        // Cria registro de SAÍDA
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'Saida', // Sem acento, perfeito!
            'hr_registro' => $saida,
            'ip_registro' => $this->faker->ipv4(),
        ]);
        // if ($saida->lessThan($agora) && rand(1, 100) > 10) {
        //     RegistroPonto::create([
        //         'estagiario_id' => $estagiario->id,
        //         'ds_motivo' => 'saida', // Sem acento, perfeito!
        //         'hr_registro' => $saida,
        //         'ip_registro' => $this->faker->ipv4(),
        // ]);
        if ($saida->lessThan($agora) && rand(1, 100) > 10) {
            RegistroPonto::create([
                'estagiario_id' => $estagiario->id,
                'ds_motivo' => 'saida', // Sem acento, perfeito!
                'hr_registro' => $saida,
                'ip_registro' => $this->faker->ipv4(),
                'ds_observacao' => 'saida'
            ]);
        }
    }

    private function gerarOcorrencia($estagiario)
    {
        $motivosEspeciais = ['falta', 'dispensa', 'recesso', 'folga'];

        // Escolhe um motivo aleatório da lista
        $motivo = $motivosEspeciais[array_rand($motivosEspeciais)];

        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => $motivo,
            // Horário zerado (00:00:00) do dia de hoje
            'hr_registro' => Carbon::today()->startOfDay(),
            'ip_registro' => $this->faker->ipv4(),
            'ds_observacao' => 'motivos Especiais'
        ]);
    }
}