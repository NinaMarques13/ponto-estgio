<?php

use App\Http\Controllers\EstagiariosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('views/pages/inicio', function () {
    return view('pages.inicio.inicio');
});

Route::get('views/adm', function () {
    return view('adm');
});
Route::get('/views/pages/inicio', [EstagiariosController::class, 'index'])->name('inicio.index');
Route::any('/registrar-ponto', [EstagiariosController::class, 'store'])->name('estagiarios.store');