<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstagiariosController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\CadastroController;
use App\Http\Controllers\EventosController;
use Illuminate\Support\Facades\Artisan;

Route::get('/popular-banco-secreto', function () {
    try {
        Artisan::call('db:seed', ['--force' => true]);
        return 'Banco populado com sucesso! Você já pode acessar /admin/login';
    } catch (\Exception $e) {
        return 'Erro ao popular o banco: ' . $e->getMessage();
    }
});
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
    Route::get('eventos', function () {
        return view('pages.principal.eventos');
    })->name('eventos');
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
Route::get('estagiarios-eventos', [EventosController::class, 'ListaEstagiariosEventos'])->name('estagiarios-eventos');
Route::post('salvar-evento', [EventosController::class, 'storeEvento'])->name('eventos.store');
Route::get('estagiarios/{id}/listar-eventos', [EventosController::class, 'getEventosEstagiario'])->name('eventos.listar');
Route::get('estagiarios/{id}/verificar-periodo', [EventosController::class, 'verificarPeriodo'])->name('eventos.verificar');
Route::delete('excluir-evento/{id}', [EventosController::class, 'destroyEvento'])->name('eventos.destroy');
Route::post('excluir-eventos-lote', [EventosController::class, 'destroyEventosLote'])->name('eventos.destroyLote');