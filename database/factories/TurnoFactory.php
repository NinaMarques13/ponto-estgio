<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Estagiarios\Models\Estagiario>
 */
class TurnoFactory extends Factory
{
    protected $model = \App\Domains\ControleDePonto\Models\Turno::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ds_tipo'    => 'superior', 
            'hr_entrada' => '08:00:00',
            'hr_saida'   => '14:00:00',
        ];
    }
}