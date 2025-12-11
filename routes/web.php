<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
<<<<<<< HEAD
Route::get('views/pages/inicio', function () {
    return view('pages.inicio.inicio');
});

Route::get('views/adm', function () {
    return view('adm');
=======
});
Route::get('views/pages/inicio', function () {
    return view('pages.inicio.inicio');
>>>>>>> 4c78dd4 (table adm e models criados)
});