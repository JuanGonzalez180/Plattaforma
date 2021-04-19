<?php

use Illuminate\Support\Facades\Route;
use WebControllers\user\UsersController;
use WebControllers\country\CountryController;
use WebControllers\category\CategoryController;
use WebControllers\categoryservices\CategoryServicesController;
use WebControllers\typeproject\TypeProjectController;
use WebControllers\typesentity\TypesEntityController;
use WebControllers\staticcontent\StaticContentController;
use WebControllers\socialnetworks\SocialNetworksController;
use App\Http\Controllers\WebControllers\HomeController;
use WebControllers\stripe\PlanController;
use WebControllers\stripe\ProductsStripeController;
use WebControllers\stripe\SubscriptionController;

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