<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroPonto extends Model
{
    use HasFactory;

    protected $table = 'registro_ponto'; // Importante pois o Laravel procuraria 'registro_pontos'

    protected $fillable = [
        'estagiario_id',
        'ds_motivo',
        'hr_registro',
        'ip_registro',
    ];

    public function estagiario()
    {
        return $this->belongsTo(Estagiario::class, 'estagiario_id');
    }
}