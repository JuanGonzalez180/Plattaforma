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
})->name('welcome');

Route::get('/usuarios', 'user\UserController')->name('usuarios');

Route::get('/categorias', function () {
    return view('categorias');
})->name('categorias');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('tipos-entidad', 'typesentity\TypesEntityController')
        ->names('typesentity')
        ->parameters(['tipos-entidad' => 'entity']);