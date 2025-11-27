<?php

use App\Http\Middleware\Benchmark;
use Illuminate\Support\Facades\Route;

Route::get('admin1', function () {
    // sleep(3); // Simulate a delay for testing
    echo 'Admin Dashboard';

})->name('admin.dashboard')->middleware('benchmark');

Route::get('admin2', 'HomeController@index');
Route::get('/home/dbTest', 'HomeController@dbTest');
