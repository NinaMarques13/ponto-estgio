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
        Schema::create('estagiarios', function (Blueprint $table) {
            $table->id();
            $table->string('nm_estagiarios', 100);
            $table->string('nr_matricula', 14)->unique();
            $table->string('nm_setor', 255);
            $table->string('nr_telefone', 11)->unique();
            $table->string('nm_email', 255)->unique();
            $table->boolean('ds_situacao')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estagiarios');
    }
};
