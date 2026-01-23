<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; 
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ec7a937 (feat: AdminLevel,ReportController,AuthController, atualização de código.)
Route::get('views/pages/inicio', function () {
    return view('pages.inicio.inicio');
});

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ec7a937 (feat: AdminLevel,ReportController,AuthController, atualização de código.)
// Rotas para o admin.
Route::get('admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

<<<<<<< HEAD
=======
//middleware adm
Route::middleware(['auth', 'checklevel:2'])->group(function () {
    Route::get('/exportar-excel', [ReportController::class, 'exportExcel']);
    Route::get('/exportar-pdf', [ReportController::class, 'exportPdf']);

    Route::get('views/principal/dashboard', function () {
    return view('pages.principal.dashboard');
    });
});
>>>>>>> ec7a937 (feat: AdminLevel,ReportController,AuthController, atualização de código.)



Route::get('views/login/adm', function () {
    return view('pages.login.adm');
});

<<<<<<< HEAD
Route::get('views/principal/dashboard', function () {
    return view('pages.principal.dashboard');
});
=======

>>>>>>> ec7a937 (feat: AdminLevel,ReportController,AuthController, atualização de código.)

Route::get('views/principal/cadastro', function () {
    return view('pages.principal.cadastro');
});
Route::get('/views/pages/inicio', [App\Http\Controllers\EstagiariosController::class, 'index'])->name('inicio.index');
route::any('/registrar-ponto', [App\Http\Controllers\EstagiariosController::class, 'store'])->name('registrar-ponto');
<<<<<<< HEAD
=======
Route::get('views/adm', function () {
    return view('adm');
});
Route::get('/views/pages/inicio', [EstagiariosController::class, 'index'])->name('inicio.index');
Route::any('/registrar-ponto', [EstagiariosController::class, 'store'])->name('estagiarios.store');
>>>>>>> db14817 (tela inicial funcional)
=======
>>>>>>> a8334e6 (Revert "Merge branch 'feature/tela-inicial' into 'master'")
=======

>>>>>>> ec7a937 (feat: AdminLevel,ReportController,AuthController, atualização de código.)
