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

Route::group(['middleware' => ['web']], function () {
    Route::get('receipt', 'ReceiptController@show')
        ->middleware('auth')
        ->name('receipt');
});

Route::post('/response', 'KnetController@handleKnet')->name('response');
Route::get('/error', 'KnetController@error')->name('error');
