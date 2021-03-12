<?php

use Illuminate\Support\Facades\Route;
use WebControllers\user\UsersController;
use WebControllers\country\CountryController;
use WebControllers\category\CategoryController;
use WebControllers\typeproject\TypeProjectController;
use WebControllers\typesentity\TypesEntityController;
use App\Http\Controllers\WebControllers\HomeController;
use WebControllers\staticcontent\StaticContentController;
use WebControllers\socialNetworks\SocialNetworksController;

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

// Paises
Route::resource('paises', CountryController::class)
        ->names('countries')
        ->parameters(['paises' => 'country'])
        ->middleware('auth');

// Categorías
Route::resource('redessociales', SocialNetworksController::class)
        ->names('socialnetwork')
        ->parameters(['redessociales' => 'socialnetwork'])
        ->middleware('auth');

// Usuarios
Route::resource('usuarios', UsersController::class, ['only' => ['index', 'edit']])
        ->names('users')
        ->parameters(['usuarios' => 'user'])
        ->middleware('auth');

Route::post('usuarios',[App\Http\Controllers\WebControllers\user\UsersController::class, 'approve'])
        ->name('users.approve')
        ->middleware('auth');