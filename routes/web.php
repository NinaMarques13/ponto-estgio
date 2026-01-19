<?php

// use Illuminate\Support\Facades\App;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EstagiariosController;
use League\Uri\Http;

Route::get('/', function () {
    return view('welcome');
});
Route::get('views/pages/inicio', function () {
    return view('pages.inicio.inicio');
});

Route::get('views/login/adm', function () {
    return view('pages.login.adm');
});
Route::get('views/principal/export', function () {
    return view('pages.principal.export');
});
Route::get('/views/pages/inicio', [EstagiariosController::class, 'index'])->name('inicio.index');
route::any('/registrar-ponto', [EstagiariosController::class, 'store'])->name('registrar-ponto');
Route::post('/relatorio-estagiarios', [EstagiariosController::class, 'relatorioEstagiarios'])->name('relatorio.estagiarios');
Route::any('/lista-estagiarios', [EstagiariosController::class, 'listaEstagiariosDia'])->name('lista.estagiarios');
Route::get('/pesquisar-estagiarios', [EstagiariosController::class, 'pesquisarEstagiarios'])->name('filtrar.estagiarios');
