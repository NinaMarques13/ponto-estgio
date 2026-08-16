<?php

namespace App\Domains\Estagiarios\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use App\Domains\ControleDePonto\Models\Turno;

class Estagiario extends Model
{   
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\EstagiarioFactory::new();
    }

    protected $table = "estagiarios";
    protected $fillable = [
        'nm_estagiarios',
        'cpf',
        'nm_setor',
        'nr_telefone',
        'nm_email',
        'ds_situacao',    
    ];

    protected $casts = [
        'ds_situacao' => 'boolean',
    ];
    
    public function registroPonto()
    {
        // 1º parâmetro: Model filho
        // 2º parâmetro: Chave estrangeira na tabela registro_ponto
        return $this->hasMany(RegistroPonto::class, 'estagiario_id');
    }

    /**
     * Relacionamento: Um Estagiário tem MUITOS Turnos
     */
    public function turno() 
    {
        // 1º parâmetro: Model filho
        // 2º parâmetro: Chave estrangeira na tabela turnos (Você criou no plural na migration)
        return $this->hasMany(Turno::class, 'estagiario_id'); 
    }
}
