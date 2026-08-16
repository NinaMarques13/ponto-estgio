<?php

namespace App\Domains\ControleDePonto\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Estagiarios\Models\Estagiario;

class RegistroPonto extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return \Database\Factories\RegistroPontoFactory::new();
    }

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
