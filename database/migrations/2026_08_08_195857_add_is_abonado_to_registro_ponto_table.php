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
        Schema::table('registro_ponto', function (Blueprint $table) {
            $table->boolean('is_abonado')->default(false)->after('ds_observacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registro_ponto', function (Blueprint $table) {
            $table->dropColumn('is_abonado');
        });
    }
};
