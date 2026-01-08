<?php

// use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; 
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('views/pages/inicio', function () {
    return view('pages.inicio.inicio');
});

// Rotas para o admin.
Route::get('admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::get('views/login/adm', function () {
    return view('pages.login.adm');
});
Route::get('views/principal/dashboard', function () {
    return view('pages.principal.dashboard');
});

Route::get('views/principal/cadastro', function () {
    return view('pages.principal.cadastro');
});
Route::get('views/principal/exportacao', function () {
    return view('pages.principal.exportacao');
});
Route::get('views/principal/export', function () {
    return view('pages.principal.export');
});
Route::get('/views/pages/inicio', [App\Http\Controllers\EstagiariosController::class, 'index'])->name('inicio.index');
route::any('/registrar-ponto', [App\Http\Controllers\EstagiariosController::class, 'store'])->name('registrar-ponto');
Route::get('/relatorio-estagiarios', [App\Http\Controllers\EstagiariosController::class, 'relatorioEstagiarios'])->name('relatorio.estagiarios');
Route::get('/relatorio-registros', [App\Http\Controllers\EstagiariosController::class, 'relatorioRegistros'])->name('relatorio.registros');
Route::get('/relatorio-recesso', [App\Http\Controllers\EstagiariosController::class, 'relatorioRecesso'])->name('relatorio.recesso');
Route::get('/relatorio-atestados', [App\Http\Controllers\EstagiariosController::class, 'relatorioAtestado'])->name('relatorio.atestados');
Route::get('/relatorio-folgas', [App\Http\Controllers\EstagiariosController::class, 'relatorioFolga'])->name('relatorio.folgas');
Route::get('/relatorio-dispensas', [App\Http\Controllers\EstagiariosController::class, 'relatorioDispensa'])->name('relatorio.dispensas');
Route::get('/relatorio-faltas', [App\Http\Controllers\EstagiariosController::class, 'relatorioFalta'])->name('relatorio.faltas');
Route::get('/lista-estagiarios', [App\Http\Controllers\EstagiariosController::class, 'listaEstagiariosDia'])->name('lista.estagiarios');
