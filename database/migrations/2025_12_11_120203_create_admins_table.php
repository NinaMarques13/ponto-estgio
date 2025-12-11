<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('cpf',11) -> unique();
            $table->string('password', 255);
            $table->string('name', 99)-> nullable();
            $table->string('email', 255)-> nullable() -> unique();
            $table->timestamps();

            $table->index('name');
            $table->index('email');
            /*
                 <--! IMPORTANTE, UNIQUE JÁ CRIA UM INDEX, PRA CRIAR UM TABLE INDEX TEM QUE SER UM DADO SEM UNIQUEEEE !-->
            como usar o index? 
            você vai usar ele para uma busca rápida no banco de X usuário, por exemplo:
            "Ah, quero achar o joaozinho das couve no banco de dados..."
            e então, rodará o seguinte código:
            Admin::where('cpf', '12345678901')->first();
            aqui ele vai encontrar o joaozinho em questão de vapt vupt, demorô?
            Com o email é a mesma coisa,
            Admin::where('email','joaodascouvenaoereal@gmail.com')->first();
            aqui ele faz a mesma coisa que você fez acima, só que com o email.
            mas se por acaso você quiser encontrar todos os seres humanos com Jõao no nome, dai o comando muda.
            Admin::where('name','like','%joao%')->get();
            */
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
