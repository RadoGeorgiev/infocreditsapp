<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/home', 'App\Http\Controllers\HomeController@index');

Route::post('/new', 'App\Http\Controllers\HomeController@createNewCreditEntry');
Route::get('/payment', 'App\Http\Controllers\HomeController@makeCreditPayment');