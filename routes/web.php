<?php

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
