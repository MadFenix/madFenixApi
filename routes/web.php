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
    return view('welcome');
});

/*
Route::get('/test-ia', function () {
    return view('test-ia');
});
Route::get('/test-ia-v2', function () {
    return view('test-ia-v2');
});
Route::get('/test-ia-v3', function () {
    return view('test-ia-v3');
});
*/

Route::get('password-reset', function () {
    return redirect(config('madfenix.spa_website') . '/recordar-password-formulario');
})->name('password.reset');
