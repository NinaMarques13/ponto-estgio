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
        Admin::updateOrCreate(
            ['cpf' => '12345678904'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('adm123'),
                'level' => 1,
            ]
        );

        Admin::updateOrCreate(
            ['cpf' => '12345678901'],
            [
                'name' => 'Admin Comum 1',
                'email' => 'admin1@admin.com',
                'password' => Hash::make('adm123'),
                'level' => 2,
            ]
        );

        Admin::updateOrCreate(
            ['cpf' => '12345678902'],
            [
                'name' => 'Admin Comum 2',
                'email' => 'admin2@admin.com',
                'password' => Hash::make('adm123'),
                'level' => 2,
            ]
        );
    }
}
