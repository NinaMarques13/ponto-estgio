<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $fillable = [
        'estagiarios_id', // Note o plural aqui para bater com sua migration
        'ds_tipo',
        'hr_entrada',
        'hr_saida',
    ];

    public function estagiario()
    {
        // Relacionamento inverso: Turno PERTENCE A um Estagiario
        return $this->belongsTo(Estagiario::class, 'estagiarios_id');
    }
}
