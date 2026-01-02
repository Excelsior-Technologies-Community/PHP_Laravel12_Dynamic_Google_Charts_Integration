<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleChartController;


Route::get('/', function () {
    return view('welcome');
});


Route::get('chart', [GoogleChartController::class, 'index']);
