<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});
Route::get('views/pages/inicio', function () {
    return view('pages.inicio.inicio');
});

Route::get('views/adm', function () {
    return view('adm');
});

/* área teste dos admin papai*/

Route::get('/teste-login', function () {
    $check = Auth::guard('admin')->attempt([
        'cpf' => '12345678904', 
        'password' => 'adm123'
    ]);
    
    return $check ? 'Boa!' : 'Falha';
});