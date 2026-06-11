<?php

use App\Http\Controllers\GeoCheckerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GeoCheckerController::class, 'index']);
Route::post('/check', [GeoCheckerController::class, 'check'])->middleware('throttle:5,60');
