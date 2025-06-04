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

// Route::post('getOrder', 'HomeController@getOrder')->name('getOrder');
// Route::get('getOrder/{id?}/{name?}', 'HomeController@getOrder')->name('getOrder');


Route::get('getOrder/{id?}/{name?}',function($id,$name){
    return 'Order ID: '.$id.' and name: '.$name;
})->name('getOrderWithClosure')->where('id', '[0-9]+');

Route::get('getUser', 'HomeController@getUser')->name('getUser');
Route::get('getUrl', function(){
    // return redirect()->route('getUser', ['id' => 30]);
    return \route('getUser', ['id' => 30],false);
    // return redirect()->to(\route('getUser', ['id' => 3]));
});

Route::get('getProduct', 'HomeController@modelTest')->name('getProduct');

Route::get('testCollection','HomeController@testCollection')->name('testCollection');
