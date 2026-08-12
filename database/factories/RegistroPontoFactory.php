<?php

namespace Database\Factories;

use App\Models\RegistroPonto;
use App\Models\Estagiario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RegistroPontoFactory extends Factory
{
    protected $model = RegistroPonto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $motivos = ['entrada', 'saida', 'falta', 'folga', 'atestado', 'recesso', 'dispensa'];
        
        return [
            'estagiario_id' => Estagiario::factory(),
            'ds_motivo' => $this->faker->randomElement($motivos),
            'hr_registro' => Carbon::now()->subDays($this->faker->numberBetween(0, 30))->setTime(
                $this->faker->numberBetween(6, 18),
                $this->faker->numberBetween(0, 59)
            ),
            'ip_registro' => $this->faker->ipv4(),
            'ds_observacao' => $this->faker->optional()->text(50),
        ];
    }
}
