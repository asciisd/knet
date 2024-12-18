<?php

/*
|--------------------------------------------------------------------------
| knet Routes
|--------------------------------------------------------------------------
|
| Here is where you can register routes for your package.
|
*/

use Asciisd\Knet\Http\Middleware\VerifyKnetResponseSignature;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('receipt', 'ReceiptController@show')->name('receipt');
});

Route::post('/handle', 'KnetController@handle')->name('handle');
Route::get('/error', 'KnetController@error')->name('error');
Route::middleware([VerifyKnetResponseSignature::class])
    ->post('/response', 'KnetController@response')
    ->name('response');
