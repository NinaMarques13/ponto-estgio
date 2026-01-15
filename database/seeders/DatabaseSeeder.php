<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        // User::factory(10)->create();

<<<<<<< HEAD
        Estagiario::factory(10)
                ->hasTurno(2)
                ->hasRegistroPonto(5)
                ->create();
    } 
=======
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
>>>>>>> a8334e6 (Revert "Merge branch 'feature/tela-inicial' into 'master'")
}
 
