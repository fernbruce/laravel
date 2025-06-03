<?php

use Illuminate\Support\Facades\Route;

Route::get('admin1', function () {
    // sleep(3); // Simulate a delay for testing
    echo 'Admin Dashboard';

})->name('admin.dashboard');