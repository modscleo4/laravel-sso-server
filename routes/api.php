<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('sso')->name('sso.')->group(function () {
    Route::post('login', 'ServerController@login');
    Route::post('logout', 'ServerController@logout');
    Route::get('attach', 'ServerController@attach');
    Route::get('userInfo', 'ServerController@userInfo');
});
