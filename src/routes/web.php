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

Route::get('/', function () {
    return view('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::prefix('reminders')->group(function () {
    Route::get('/', function () {
        return view('reminders.list');
    });
    Route::get('/create', function () {
        return view('reminders.create');
    });
    Route::get('/update', function () {
        return view('reminders.update');
    });
});