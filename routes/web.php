<?php

/*
|--------------------------------------------------------------------------
| knet Routes
|--------------------------------------------------------------------------
|
| Here is where you can register routes for your package.
|
*/

use Illuminate\Support\Facades\Route;

Route::prefix('knet')->group(function () {
    Route::post('/response', 'Asciisd\Knet\Http\Controllers\KnetController@handleKnet')->name('knet.response');
    Route::post('/error', 'Asciisd\Knet\Http\Controllers\KnetController@error')->name('knet.error');
});