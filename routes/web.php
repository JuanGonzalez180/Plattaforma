<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebControllers\HomeController;
use WebControllers\category\CategoryController;
use WebControllers\typeproject\TypeProjectController;
use WebControllers\typesentity\TypesEntityController;
use WebControllers\staticcontent\StaticContentController;

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

// Route::get('/usuarios', 'user\UserController')->name('usuarios');


Auth::routes(['register' => false]);

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Categorías
Route::resource('categorias', CategoryController::class)
        ->names('category')
        ->parameters(['categorias' => 'category'])
        ->middleware('auth');

// Tipo de Proyectos
Route::resource('tipos-proyectos', TypeProjectController::class)
        ->names('typeproject')
        ->parameters(['tipos-proyecto' => 'project'])
        ->middleware('auth');

// Tipo de Entidades
Route::resource('tipos-entidad', TypesEntityController::class)
        ->names('typesentity')
        ->parameters(['tipos-entidad' => 'typeEntity'])
        ->middleware('auth');

// Contenido estatico
Route::resource('contenido-estatico', StaticContentController::class)
        ->names('staticcontent')
        ->parameters(['contenido-estatico' => 'staticContent'])
        ->middleware('auth');