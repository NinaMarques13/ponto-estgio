<?php

namespace Tests\Unit;

use App\Domains\Admins\Models\Admin;
use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use App\Domains\ControleDePonto\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase; // Usando Tests\TestCase para ter banco de dados no Laravel

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_admin()
    {
        $admin = Admin::factory()->create([
            'name' => 'Teste Admin',
            'email' => 'admin.teste@teste.com'
        ]);

        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertEquals('Teste Admin', $admin->name);
        $this->assertEquals('admin.teste@teste.com', $admin->email);
    }

    public function test_pode_criar_usuario()
    {
        // Usa create mass assignment se o factory não estiver definido
        $user = User::create([
            'name' => 'Teste User',
            'email' => 'user.teste@teste.com',
            'password' => bcrypt('password')
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Teste User', $user->name);
    }

    public function test_relacionamento_estagiario_possui_registros()
    {
        $estagiario = Estagiario::factory()->create();
        
        $registro = RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => 'entrada',
            'hr_registro' => now(),
            'ip_registro' => '127.0.0.1',
        ]);

        $this->assertInstanceOf(Estagiario::class, $estagiario);
        $this->assertTrue($estagiario->registroPonto->contains($registro));
        
        // Verifica a relação inversa
        $this->assertInstanceOf(Estagiario::class, $registro->estagiario);
        $this->assertEquals($estagiario->id, $registro->estagiario->id);
    }

    public function test_pode_criar_turno()
    {
        $estagiario = Estagiario::factory()->create();
        
        $turno = Turno::create([
            'estagiario_id' => $estagiario->id,
            'ds_tipo' => 'Matutino',
            'hr_entrada' => '08:00',
            'hr_saida' => '12:00',
        ]);

        $this->assertInstanceOf(Turno::class, $turno);
        $this->assertEquals('Matutino', $turno->ds_tipo);
    }
}
