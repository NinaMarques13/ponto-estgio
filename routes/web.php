<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});
Route::get('views/pages/inicio', function () {
    return view('pages.inicio.inicio');
});

Route::get('views/login/adm', function () {
    return view('pages.login.adm');
});
Route::get('views/principal/exportacao', function () {
    return view('pages.principal.exportacao');
});

Route::get('/views/pages/inicio', [App\Http\Controllers\EstagiariosController::class, 'index'])->name('inicio.index');
route::any('/registrar-ponto', [App\Http\Controllers\EstagiariosController::class, 'store'])->name('registrar-ponto');
