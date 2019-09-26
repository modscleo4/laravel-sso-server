<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes([
    'register' => false, // Registration Routes
    'reset' => true, // Password Reset Routes
    'verify' => false, // Email Verification Routes
]);

Route::prefix('user')->name('user.')->group(function () {
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('', 'Auth\ChangePasswordController@showChangeForm')->name('form');
        Route::put('update', 'Auth\ChangePasswordController@change')->name('update');
    });
});

Route::get('loginForm', 'ServerController@loginForm');
