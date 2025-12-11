<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('views/pages', function () {
    return view('pages.inicio');
});