<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleChartController;
use App\Http\Controllers\EnhancedChartController;

Route::get('/', function () {
    return view('welcome');
});

// Original routes
Route::get('chart', [GoogleChartController::class, 'index'])->name('chart.index');
Route::get('quarter-chart', [GoogleChartController::class, 'quarterChart'])->name('chart.quarter');

// New enhanced routes
Route::get('dashboard', [EnhancedChartController::class, 'dashboard'])->name('dashboard');
Route::get('api/chart-data', [EnhancedChartController::class, 'getChartData'])->name('api.chart-data');
Route::get('export-data', [EnhancedChartController::class, 'exportData'])->name('export.data');