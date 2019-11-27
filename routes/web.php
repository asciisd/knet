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
    Route::post('/response', 'Asciisd\Knet\Http\Controllers\KnetController@response')->name('knet.response');

    Route::middleware('auth')->group(function() {
        Route::get('/', 'Asciisd\Knet\Http\Controllers\KnetController@index')->name('knet');
        Route::post('/', 'Asciisd\Knet\Http\Controllers\KnetController@charge')->name('knet.charge');
        Route::post('/error', 'Asciisd\Knet\Http\Controllers\KnetController@error')->name('knet.error');
    });
});