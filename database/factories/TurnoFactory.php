<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estagiario>
 */
class TurnoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ds_tipo'    => 'MATUTINO', // ou use $this->faker->randomElement(['MANHA', 'TARDE'])
            'hr_entrada' => '08:00:00',
            'hr_saida'   => '12:00:00',
        ];
    }
}
