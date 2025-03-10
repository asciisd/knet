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
//use Asciisd\Knet\Http\Middleware\VerifyKnetResponseSignature; TODO: add this line as a middleware
use Illuminate\Support\Facades\Route;

Route::post('/handle', HandleController::class)->name('handle');
Route::post('/error', ErrorController::class)->name('error');
Route::post('/response', ResponseController::class)
    ->name('response.store');

Route::get('/response', ResponseController::class)
    ->name('response.show');
