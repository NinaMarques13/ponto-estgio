<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

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
