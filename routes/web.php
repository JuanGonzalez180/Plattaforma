<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebControllers\user\UsersController;
use App\Http\Controllers\WebControllers\country\CountryController;
use App\Http\Controllers\WebControllers\category\CategoryController;
use App\Http\Controllers\WebControllers\categoryservices\CategoryServicesController;
use App\Http\Controllers\WebControllers\typeproject\TypeProjectController;
use App\Http\Controllers\WebControllers\typesentity\TypesEntityController;
use App\Http\Controllers\WebControllers\staticcontent\StaticContentController;
use App\Http\Controllers\WebControllers\socialnetworks\SocialNetworksController;
use App\Http\Controllers\WebControllers\HomeController;
use App\Http\Controllers\WebControllers\stripe\PlanController;
use App\Http\Controllers\WebControllers\stripe\ProductsStripeController;
use App\Http\Controllers\WebControllers\stripe\SubscriptionController;
use App\Http\Controllers\WebControllers\brands\BrandsController;

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


Route::group(['middleware' => 'auth'], function() {
        // Categorías
        Route::resource('categorias', CategoryController::class)
                ->names('category')
                ->parameters(['categorias' => 'category']);

        // Categorías
        Route::resource('categorias_servicios', CategoryServicesController::class)
                ->names('categoryservices')
                ->parameters(['categorias_servicios' => 'categoryservices']);

        // Marcas
        Route::resource('brands', BrandsController::class)
                ->names('brand')
                ->parameters(['brands' => 'brand']);

        // Tipo de Proyectos
        Route::resource('tipos-proyectos', TypeProjectController::class)
                ->names('typeproject')
                ->parameters(['tipos-proyecto' => 'project']);

        // Tipo de Entidades
        Route::resource('tipos-entidad', TypesEntityController::class)
                ->names('typesentity')
                ->parameters(['tipos-entidad' => 'typeEntity']);

        // Contenido estatico
        Route::resource('contenido-estatico', StaticContentController::class)
                ->names('staticcontent')
                ->parameters(['contenido-estatico' => 'staticContent']);

        // Paises
        Route::resource('paises', CountryController::class)
                ->names('countries')
                ->parameters(['paises' => 'country']);

        // Categorías
        Route::resource('redessociales', SocialNetworksController::class)
                ->names('socialnetwork')
                ->parameters(['redessociales' => 'socialnetwork']);

        // Usuarios
        Route::resource('usuarios', UsersController::class, ['only' => ['index', 'edit']])
                ->names('users')
                ->parameters(['usuarios' => 'user']);

        Route::post('usuarios',[App\Http\Controllers\WebControllers\user\UsersController::class, 'approve'])
                ->name('users.approve');

        // Stripe
        Route::resource('productos-stripe', ProductsStripeController::class)
                ->names('products_stripe')
                ->parameters(['productos-stripe' => 'products_stripe']);

        Route::resource('planes-stripe', PlanController::class)
                ->names('plans')
                ->parameters(['planes' => 'plans']);

        /*Route::resource('subscriptions', SubscriptionController::class)
                ->names('subscription')
                ->parameters(['subscription' => 'subscription']);*/
});