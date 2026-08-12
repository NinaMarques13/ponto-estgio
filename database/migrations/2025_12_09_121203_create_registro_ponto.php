<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("registro_ponto", function (Blueprint $table) {
            $table->id();
            $table->foreignId('estagiario_id')->constrained('estagiarios')->onDelete('cascade');
            $table->timestamp('hr_registro');
            $table->enum('ds_motivo', ['entrada', 'saida', 'folga', 'dispensa', 'recesso', 'falta', 'atestado']);
            $table->ipAddress('ip_registro');
            $table->string('ds_observacao', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_ponto');
    }
};
