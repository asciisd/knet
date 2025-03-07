<?php

/*
|--------------------------------------------------------------------------
| knet Routes
|--------------------------------------------------------------------------
|
| Here is where you can register routes for your package.
|
*/

use Asciisd\Knet\Http\Controllers\ErrorController;
use Asciisd\Knet\Http\Controllers\HandleController;
use Asciisd\Knet\Http\Controllers\ResponseController;
use Asciisd\Knet\Http\Middleware\VerifyKnetResponseSignature;
use Illuminate\Support\Facades\Route;

Route::post('/handle', HandleController::class)->name('handle');
Route::post('/error', ErrorController::class)->name('error');
Route::middleware([VerifyKnetResponseSignature::class])
    ->post('/response', ResponseController::class)
    ->name('response.store');

Route::middleware([VerifyKnetResponseSignature::class])
    ->get('/response', ResponseController::class)
    ->name('response.show');
