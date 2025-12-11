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
        Schema::create("registro_ponto", function (Blueprint $table) {
            $table->id();
            $table->foreignId('estagiario_id')->constrained('estagiarios')->onDelete('cascade');
            $table->string('ds_motivo', 55);
            $table->timestamp('hr_registro');
            $table->ipAddress('ip_registro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
