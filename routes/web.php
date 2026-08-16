<?php

use Illuminate\Support\Facades\Route;
use App\Domains\ControleDePonto\Controllers\PontoController;
use App\Domains\Admins\Controllers\LoginController;
use App\Domains\Admins\Controllers\RegistroAdminController;
use App\Domains\Estagiarios\Controllers\CadastroController;
use App\Domains\Eventos\Controllers\EventosController;
use Illuminate\Support\Facades\Artisan;

Route::get('/popular-banco-secreto', function () {
    try {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AdminSeeder', '--force' => true]);
        return 'Banco populado com sucesso! Você já pode acessar /admin/login';
    } catch (\Throwable $e) {
        return 'Erro ao popular o banco: ' . $e->getMessage();
    }
});
Route::get('/popular-banco-estagiarios', function () {
    try {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PontoHojeSeeder', '--force' => true]);
        return 'Banco populado com sucesso! Você já pode ver os estágiários';
    } catch (\Throwable $e) {
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
    
    Route::get('admin/register', [RegistroAdminController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('admin/register', [RegistroAdminController::class, 'register'])->name('admin.register.submit');
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

Route::get('inicio', [PontoController::class, 'index'])->name('inicio.index');
Route::any('registrar-ponto', [PontoController::class, 'store'])->name('registrar-ponto');
Route::any('lista-estagiarios', [PontoController::class, 'listaEstagiariosDia'])->name('lista.estagiarios');
Route::put('atualizar-estagiarios/{id}', [PontoController::class, 'atualizarEstagiario'])->name('atualizar-estagiarios');
Route::get('estagiarios-cadastrados', [CadastroController::class, 'listagemCadastrados'])->name('estagiarios-cadastrados');
Route::post('cadastrar-estagiario', [CadastroController::class, 'storeEstagiario'])->name('estagiarios.store');
Route::put('atualizar-cadastro/{id}', [CadastroController::class, 'updateCadastro'])->name('estagiarios.update');
Route::put('desativar-estagiario/{id}', [CadastroController::class, 'desativarCadastro'])->name('estagiarios.desativar');
Route::post('processar-qrcode', [PontoController::class, 'processarQrcode'])->name('processar.qrcode');
Route::get('estagiarios-eventos', [EventosController::class, 'ListaEstagiariosEventos'])->name('estagiarios-eventos');
Route::post('salvar-evento', [EventosController::class, 'storeEvento'])->name('eventos.store');
Route::get('estagiarios/{id}/listar-eventos', [EventosController::class, 'getEventosEstagiario'])->name('eventos.listar');
Route::get('estagiarios/{id}/verificar-periodo', [EventosController::class, 'verificarPeriodo'])->name('eventos.verificar');
Route::delete('excluir-evento/{id}', [EventosController::class, 'destroyEvento'])->name('eventos.destroy');
Route::post('excluir-eventos-lote', [EventosController::class, 'destroyEventosLote'])->name('eventos.destroyLote');