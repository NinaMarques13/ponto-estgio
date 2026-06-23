<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstagiariosController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\CadastroController;

Route::get('/', function () {
    return view('pages.inicio.inicio');
})->name('home');

// Rotas de autenticação admin
Route::middleware('guest:admin')->group(function () {
    Route::get('admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
});

Route::middleware('auth:admin')->group(function () {
    Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
    Route::get('dashboard', function () {
        return view('pages.principal.dashboard');
    })->name('dashboard');
    Route::get('cadastro', function () {
        return view('pages.principal.cadastro');
    })->name('cadastro');
    Route::get('admin/export', function () {
        return view('pages.principal.export');
    })->name('export');
});

Route::get('inicio', [EstagiariosController::class, 'index'])->name('inicio.index');
Route::any('registrar-ponto', [EstagiariosController::class, 'store'])->name('registrar-ponto');
Route::any('lista-estagiarios', [EstagiariosController::class, 'listaEstagiariosDia'])->name('lista.estagiarios');
Route::put('atualizar-estagiarios/{id}', [EstagiariosController::class, 'atualizarEstagiario'])->name('atualizar-estagiarios');
Route::get('estagiarios-cadastrados', [CadastroController::class, 'listagemCadastrados'])->name('estagiarios-cadastrados');
Route::post('cadastrar-estagiario', [CadastroController::class, 'storeEstagiario'])->name('estagiarios.store');
Route::put('atualizar-cadastro/{id}', [CadastroController::class, 'updateCadastro'])->name('estagiarios.update');
Route::put('desativar-estagiario/{id}', [CadastroController::class, 'desativarCadastro'])->name('estagiarios.desativar');
Route::post('processar-qrcode', [EstagiariosController::class, 'processarQrcode'])->name('processar.qrcode');
