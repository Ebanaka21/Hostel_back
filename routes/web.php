<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    // Всегда возвращаем React SPA
    return view('welcome');
});



