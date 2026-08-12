<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroPonto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registro_ponto'; // Importante pois o Laravel procuraria 'registro_pontos'
    
    public $timestamps = true;
    protected $fillable = [
        'estagiario_id',
        'ds_motivo',
        'hr_registro',
        'ip_registro',
        'ds_observacao',
        'is_abonado'
    ];

    protected $casts = [
        'hr_registro' => 'datetime',
        'is_abonado' => 'boolean'
    ];

    public function estagiario()
    {
        return $this->belongsTo(Estagiario::class, 'estagiario_id');
    }
}
