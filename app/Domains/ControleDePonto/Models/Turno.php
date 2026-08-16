<?php

namespace App\Domains\ControleDePonto\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Estagiarios\Models\Estagiario;

class Turno extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\TurnoFactory::new();
    }

    protected $fillable = [
        'estagiario_id',
        'ds_tipo',
        'hr_entrada',
        'hr_saida',
    ];

    public function estagiario()
    {
        return $this->belongsTo(Estagiario::class, 'estagiario_id');
    }
}
