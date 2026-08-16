<?php

namespace App\Domains\Admins\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;




class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected static function newFactory()
    {
        return \Database\Factories\AdminFactory::new();
    }

    protected $fillable = [

        'cpf',
        'password',
        'name',
        'email',
        'level',
    ];

    protected $hidden = [
        
        'password',
    ];

    protected $casts = [

        'password' => 'hashed',
    ];

}
