<?php

namespace Database\Factories;

use App\Domains\Estagiarios\Models\Estagiario;
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
            'nm_estagiarios' => $this->faker->name,
            'cpf' => $this->faker->numerify('###.###.###-##'),
            'nm_setor' => $this->faker->word,
            'nr_telefone' => $this->faker->numerify('###########'),
            'nm_email' => $this->faker->unique()->safeEmail,
            'ds_situacao' => true,
        ];
    }
}
