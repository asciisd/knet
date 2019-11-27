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
    Route::post('/response', 'KnetController@response')->name('knet.response');

    Route::middleware('auth')->group(function() {
        Route::get('/', 'KnetController@index')->name('knet');
        Route::post('/', 'KnetController@charge')->name('knet.charge');
        Route::post('/error', 'KnetController@error')->name('knet.error');
    });
});