<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebControllers\user\UsersController;
use App\Http\Controllers\WebControllers\company\CompanyController;
use App\Http\Controllers\WebControllers\project\ProjectController;
use App\Http\Controllers\WebControllers\tender\TenderController;
use App\Http\Controllers\WebControllers\blog\BlogController;
use App\Http\Controllers\WebControllers\portfolio\PortfolioController;
use App\Http\Controllers\WebControllers\product\ProductController;
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
                
        Route::post('/category/childs', [CategoryController::class, 'getCategoryChilds'])
                ->name('category.childs');
        
        // Categorías servicios
        Route::resource('categorias_servicios', CategoryServicesController::class)
                ->names('categoryservices')
                ->parameters(['categorias_servicios' => 'categoryservices']);
        
        Route::post('/categoryservices/childs', [CategoryServicesController::class, 'getCategoryServiceChilds'])
                ->name('category.services.childs');
        
        // Marcas
        Route::resource('brands', BrandsController::class)
                ->names('brand')
                ->parameters(['brands' => 'brand']);

        Route::get('/company/brand/{id}', [BrandsController::class, 'indexCompanyBrand'])->name('company-brand-id');

        // Tipo de Proyectos
        Route::resource('tipos-proyectos', TypeProjectController::class)
                ->names('typeproject')
                ->parameters(['tipos-proyecto' => 'project']);

        Route::post('/type/project/childs', [TypeProjectController::class, 'getTypeProyectChilds'])
                ->name('type.projects.childs');

        // Tipo de Entidades
        Route::resource('tipos-entidad', TypesEntityController::class)
                ->names('typesentity')
                ->parameters(['tipos-entidad' => 'typeEntity']);

        // Compañias
        Route::resource('companias', CompanyController::class)
                ->names('companies')
                ->parameters(['companias' => 'companies']);

        Route::post('companies/edit/status',[CompanyController::class, 'editStatus'])
                ->name('company.edit.status');

        Route::get('/company/{type}', [CompanyController::class, 'getCompanyType'])->name('companies-type');

        // Licitaciones
        Route::resource('licitaciones', TenderController::class, ['only' => ['edit','show']])
                ->names('tender')
                ->parameters(['licitaciones' => 'tender']);

        Route::get('/tender/{type}/{id}', [TenderController::class, 'index'])->name('tender-company-id');

        Route::post('/tender/decline',[TenderController::class, 'updateStatusDecline'])
                ->name('tender.decline');

        // Productos/Servicios
        Route::get('/company/{type}/{id}', [ProductController::class, 'indexType'])->name('product-company-id');
        
        Route::resource('product', ProductController::class, ['only' => ['edit','show','update']])
                ->names('productos')
                ->parameters(['product' => 'productos']);

        
        // Proyectos
        Route::resource('proyecto', ProjectController::class, ['only' => ['edit','show']])
                ->names('project')
                ->parameters(['proyecto' => 'project']);

        Route::get('/project/company/{id}', [ProjectController::class, 'index'])->name('project-company-id');

        Route::post('/project/edit/visible',[ProjectController::class, 'editVisible'])
                ->name('project.edit.visible');

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

        // Categorías
        Route::resource('redessociales', SocialNetworksController::class)
                ->names('socialnetwork')
                ->parameters(['redessociales' => 'socialnetwork']);

        // Blogs

        Route::resource('blog', BlogController::class, ['only' => ['edit','show']])
                ->names('blog')
                ->parameters(['blog' => 'blog']);

        Route::get('/blog/company/{id}', [BlogController::class, 'index'])
                ->name('blog.company.id');

        // portafolio
        Route::get('/portfolio/company/{id}', [PortfolioController::class, 'index'])
                ->name('portfolio.company.id');

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