<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('estagiarios', function (Blueprint $table) {
        $table->renameColumn('nr_matricula', 'cpf');
    });
}

public function down()
    {
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->renameColumn('cpf', 'nr_matricula');
        });
    }
};