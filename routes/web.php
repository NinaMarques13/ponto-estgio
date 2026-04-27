<?php

// use Illuminate\Support\Facades\App;

use App\Models\Estagiario;
use FontLib\Table\Type\name;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EstagiariosController;
use App\Http\Controllers\Admin\LoginController;
use League\Uri\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\DataTables\DataTables;

Route::get('/', function () {
    return view('welcome');
});
// Route::get('views/pages/inicio', function () {
//     return view('pages.inicio.inicio');
// });

// Rotas para o admin.
Route::get('admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::get('views/login/adm', function () {
    return view('pages.login.adm');
});

Route::get('views/templates/layout', function () {
    return view('pages.templates.layout');
})->name('layout');
Route::get('views/principal/dashboard', function () {
    return view('pages.principal.dashboard');
})->name('dashboard');
Route::get('views/principal/cadastro', function () {
    return view('pages.principal.cadastro');
})->name('cadastro');
Route::get('views/principal/export', function () {
    return view('pages.principal.export');
})->name('export');
Route::get('/views/pages/inicio', [EstagiariosController::class, 'index'])->name('inicio.index');
Route::any('/registrar-ponto', [EstagiariosController::class, 'store'])->name('registrar-ponto');
Route::any('/lista-estagiarios', [EstagiariosController::class, 'listaEstagiariosDia'])->name('lista.estagiarios');
Route::put('/atualizar-estagiarios/{id}', [EstagiariosController::class, 'update'])->name('atualizar-estagiarios');
Route::get('/estagiarios-cadastrados',[EstagiariosController::class,'listagemCadastrados'])->name('estagiarios-cadastrados');
Route::post('/cadastrar-estagiario',[EstagiariosController::class,'storeEstagiario'])->name('estagiarios.store');
Route::put('/atualizar-cadastro/{id}',[EstagiariosController::class,'updateCadastro'])->name('estagiarios.update');
Route::put('/desativar-estagiario/{id}', [EstagiariosController::class, 'desativarCadastro'])->name('estagiarios.desativar');


