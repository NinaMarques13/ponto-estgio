<?php

namespace Database\Factories;

use App\Models\Estagiario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class EstagiarioFactory extends Factory
{
    protected $model = Estagiario::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nm_estagiarios' => fake()->name(),
<<<<<<< HEAD
            'nr_matricula' => fake('pt_BR')->cpf(true),
=======
            'nr_matricula' => fake('pt_BR')->unique()->cpf(true),
>>>>>>> db14817 (tela inicial funcional)
            'nm_setor' => fake()->word(),
            'nr_telefone' => fake()->unique()->numerify('###########'),
            'nm_email' => fake()->unique()->safeEmail(),
            'ds_situacao' => true,
        ];
    }
}
