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
Route::get('/relatorio-estagiarios', [EstagiariosController::class, 'relatorioEstagiarios'])->name('relatorio.estagiarios');
Route::get('/relatorio-registros', [EstagiariosController::class, 'relatorioRegistros'])->name('relatorio.registros');
Route::get('/relatorio-recesso', [EstagiariosController::class, 'relatorioRecesso'])->name('relatorio.recesso');
Route::get('/relatorio-atestados', [EstagiariosController::class, 'relatorioAtestado'])->name('relatorio.atestados');
Route::get('/relatorio-folgas', [EstagiariosController::class, 'relatorioFolga'])->name('relatorio.folgas');
Route::get('/relatorio-dispensas', [EstagiariosController::class, 'relatorioDispensa'])->name('relatorio.dispensas');
Route::get('/relatorio-faltas', [EstagiariosController::class, 'relatorioFalta'])->name('relatorio.faltas');
Route::get('/lista-estagiarios', [EstagiariosController::class, 'listaEstagiariosDia'])->name('lista.estagiarios');
Route::get('/pesquisar-estagiarios', [EstagiariosController::class, 'pesquisarEstagiarios'])->name('filtrar.estagiarios');
Route::any('/pesquisar-data', [EstagiariosController::class, 'pesquisarData'])->name('pesquisar.data');
route::any('/pesquisar-mes-ano', [EstagiariosController::class, 'pesquisarMesAno'])->name('pesquisar.mes.ano');