<?php

use Illuminate\Support\Facades\Route;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/home/hello', 'HomeController@index')->name('home.hello');
Route::get('here', function () {
    return 'You are here';
})->name('here');
Route::get('there', function () {
    return 'You are there';
})->name('there');
// Route::permanentRedirect('here','there');