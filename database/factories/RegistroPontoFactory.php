<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estagiario>
 */
class RegistroPontoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ds_motivo'   => $this->faker->sentence(3),
            'hr_registro' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'ip_registro' => $this->faker->ipv4(),
        ];
    }
}
