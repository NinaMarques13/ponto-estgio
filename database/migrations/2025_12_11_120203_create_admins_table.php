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
            
            // SuperAdmin pode fazer tudo no sistema
            // admin comum só vê e gera os relatórios feitos

            $table->integer('level')->default(2);

            $table->timestapms();
            $table->index('name');
            
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
