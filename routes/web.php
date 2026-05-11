<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleChartController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('chart', [GoogleChartController::class, 'index'])->name('chart.index');
Route::get('quarter-chart', [GoogleChartController::class, 'quarterChart'])->name('chart.quarter');