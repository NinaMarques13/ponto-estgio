<?php

namespace Database\Seeders;

use App\Models\Estagiario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Estagiario::factory(10)
                ->hasTurno(1)
                ->hasRegistroPonto(5)
                ->create();
    }
}
