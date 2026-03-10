<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RegistroPonto;
use App\Models\Turno;

class Estagiario extends Model
{   
    use HasFactory;
    protected $table = "estagiarios";
    protected $fillable = [
        'nm_estagiarios',
        'nr_matricula',
        'nm_setor',
        'nr_telefone',
        'nm_email',
        'ds_situacao',      
    ];
    public function registroPonto()
    {
        // 1º parâmetro: Model filho
        // 2º parâmetro: Chave estrangeira na tabela registro_ponto
        return $this->hasMany(RegistroPonto::class, 'estagiarios_id');
    }

    /**
     * Relacionamento: Um Estagiário tem MUITOS Turnos
     */
    public function turno() 
    {
        // 1º parâmetro: Model filho
        // 2º parâmetro: Chave estrangeira na tabela turnos (Você criou no plural na migration)
        return $this->hasMany(Turno::class, 'estagiarios_id'); 
    }
}
