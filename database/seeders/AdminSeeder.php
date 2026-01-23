<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'cpf' => '12345678904',
            'name' => 'Admin',
            'email' => 'admin@adminm.com',
            'password' => 'adm123',
            'level'=>1,
        ]);
    }
}
