<?php

namespace Database\Seeders;

use App\Models\Estagiario;
use App\Models\RegistroPonto;
use App\Models\Turno;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // 1. Cria o Admin (para você poder logar)
        $this->call(AdminSeeder::class);

        // 2. Cria 5 Estagiários com seus Turnos
        $estagiarios = Estagiario::factory(15)
            ->has(Turno::factory()->count(1)) // Garante que tenham um turno definido
            ->create();

        // 3. Para cada estagiário, gera 5 dias de histórico
        foreach ($estagiarios as $estagiario) {
            
            // Loop dos últimos 5 dias (incluindo hoje)
            for ($i = 0; $i < 5; $i++) {
                $dataReferencia = Carbon::today()->subDays($i);
                
                // Sorteio: 70% chance de trabalho normal, 30% de ocorrência
                $sorteio = rand(1, 100);

                if ($sorteio <= 70) {
                    $this->criarDiaDeTrabalho($estagiario, $dataReferencia, $faker);
                } else {
                    $this->criarOcorrencia($estagiario, $dataReferencia, $faker);
                }
            }
        }
    }

    /**
     * Gera um par de Entrada e Saída (4h ou 6h de duração)
     */
    private function criarDiaDeTrabalho($estagiario, Carbon $data, $faker)
    {
        // Define carga horária (4h ou 6h)
        $cargaHoraria = rand(0, 1) ? 4 : 6;

        // Define hora de entrada aleatória (entre 07:00 e 10:00 daquele dia)
        // O clone é importante para não alterar a variável original $data no loop
        $entrada = (clone $data)->setHour(rand(7, 10))->setMinute(rand(0, 59));
        
        // Calcula a saída baseada na entrada
        $saida = (clone $entrada)->addHours($cargaHoraria);

        // Cria registro de ENTRADA
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo'     => 'Entrada', // Motivo fixo para o sistema reconhecer
            'hr_registro'   => $entrada,
            'ip_registro'   => $faker->ipv4(),
            'ds_observacao' => 'Entrada'
        ]);

        // Cria registro de SAÍDA
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo'     => 'Saida', // Motivo fixo para o sistema reconhecer
            'hr_registro'   => $saida,
            'ip_registro'   => $faker->ipv4(),
            'ds_observacao' => 'Entrada'
        ]);
    }

    /**
     * Gera um registro único de falta/dispensa/recesso
     */
    private function criarOcorrencia($estagiario, Carbon $data, $faker)
    {
        $motivosEspeciais = ['Falta', 'Dispensa', 'Recesso', 'Folga'];
        $motivo = $motivosEspeciais[array_rand($motivosEspeciais)];

        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo'     => $motivo,
            // Define o horário como 00:00 do dia da ocorrência para ficar padronizado
            'hr_registro'   => (clone $data)->startOfDay(),
            'ip_registro'   => $faker->ipv4(),
        ]);
    }
}
