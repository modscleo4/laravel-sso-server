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

Route::redirect('/', '/login');

Route::group(['middleware' => ['auth']], function () {
    Route::get('home', 'HomeController@index')->name('home');
});

Route::prefix('user')->name('user.')->group(function () {
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('', 'Auth\ChangePasswordController@showChangeForm')->name('form');
        Route::put('update', 'Auth\ChangePasswordController@change')->name('update');
    });
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('', 'Admin\UserController@index')->name('index');
        Route::get('new', 'Admin\UserController@create')->name('new');
        Route::post('', 'Admin\UserController@store')->name('store');

        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('edit', 'Admin\UserController@edit')->name('edit');
            Route::put('', 'Admin\UserController@update')->name('update');
            Route::delete('', 'Admin\UserController@destroy')->name('destroy');
        });
    });

    Route::prefix('broker')->name('broker.')->group(function () {
        Route::get('', 'Admin\BrokerController@index')->name('index');
        Route::get('new', 'Admin\BrokerController@create')->name('new');
        Route::post('', 'Admin\BrokerController@store')->name('store');

        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('edit', 'Admin\BrokerController@edit')->name('edit');
            Route::put('', 'Admin\BrokerController@update')->name('update');
            Route::delete('', 'Admin\BrokerController@destroy')->name('destroy');
        });
    });
});

Route::get('loginForm', 'ServerController@loginForm');
Route::get('passwordForm', 'ServerController@passwordForm');
