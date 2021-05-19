<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ApiControllers\typesentity\TypesEntityController;
use ApiControllers\company\CompanyController;
use ApiControllers\staticcontent\StaticContentController;
use ApiControllers\country\CountryController;
use ApiControllers\socialnetworks\SocialNetworksController;
use ApiControllers\typeproject\TypeProjectController;
use ApiControllers\category\CategoryController;
use ApiControllers\categoryservices\CategoryServicesController;
use ApiControllers\projects\ProjectsController;
use ApiControllers\projects\ProjectsFilesController;
use ApiControllers\products\ProductsController;
use ApiControllers\products\ProductsFilesController;
use ApiControllers\products\ProductsDocumentsController;
use ApiControllers\company\CompanyFilesController;
use ApiControllers\tenders\TendersController;
use ApiControllers\files\FilesController;
use ApiControllers\brands\BrandsController;
// Search
use ApiControllers\search\SearchProjectsController;
use ApiControllers\search\SearchProductsController;
// Password
use ApiControllers\password\SendCodeController;
use ApiControllers\password\CodeValidationController;
use ApiControllers\password\ChangePasswordController;
// Account
use ApiControllers\myaccount\AccountEditController;
use ApiControllers\myaccount\AccountChangePasswordController;
use ApiControllers\myaccount\AccountMyCompanyController;
use ApiControllers\myaccount\AccountMyTeamController;
use ApiControllers\myaccount\RegisterMemberController;
// Subscriptions
use ApiControllers\stripe\SubscriptionsStripeController;

use App\Http\Controllers\ApiControllers\user\UsersController;
use App\Http\Controllers\ApiControllers\projects\ProjectsController as ProjectsControllerVisible;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
/**
 * TypesEntity
 */
Route::get('/typesentity', TypesEntityController::class)->name('typesentity');

/**
 * Country
 */
Route::get('/country', CountryController::class)->name('country');

/**
 * SocialNetworks
 */
Route::get('/socialnetworks', SocialNetworksController::class)->name('socialnetworks');

/**
 * Company
 */
Route::resource('/company', CompanyController::class, ['only' => ['store']])->names('company');

/**
 * Password
 */
Route::resource('/password/sendcode', SendCodeController::class, ['only' => ['store']])->names('sendcode');
Route::resource('/password/codevalidation', CodeValidationController::class, ['only' => ['store']])->names('codevalidation');
Route::resource('/password/changepassword', ChangePasswordController::class, ['only' => ['store']])->names('changepassword');

/**
 * StaticContent
 */
Route::get('/staticcontent/{slug}', StaticContentController::class)->name('staticcontent');

/**
 * User Login
 */
// Route::resource('/login', [UsersController::class, 'authenticate'], ['only' => ['authenticate']])->names('signin');
Route::post('/login', [UsersController::class, 'authenticate'])->name('signin');

/**
 * Plans Stripe
 */
Route::get('/stripe/plans', SubscriptionsStripeController::class)->name('plans');
Route::resource('/stripe/plans', SubscriptionsStripeController::class, ['only' => ['store']])->names('plans_subscription');

/**
 * TypeProjects
 */
Route::get('/typesprojects', TypeProjectController::class)->name('typesprojects');
/**
 * Categories
 */
Route::get('/categories', CategoryController::class)->name('categories');
Route::get('/categoriesservices', CategoryServicesController::class)->name('categoriesservices');

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('user',[UsersController::class, 'getAuthenticatedUser'])->name('user');

    /**
     * My Account
     */
    Route::resource('/myaccount/changepassword', AccountChangePasswordController::class, ['only' => ['store']])->names('changepasswordaccount');
    Route::resource('/myaccount/accountedit', AccountEditController::class, ['only' => ['store']])->names('accountedit');
    Route::get('/myaccount/mycompany', AccountMyCompanyController::class)->name('mycompany');
    Route::resource('/myaccount/mycompany', AccountMyCompanyController::class, ['only' => ['store']])->names('mycompany');
    Route::resource('/myaccount/myteam', AccountMyTeamController::class, ['only' => ['index', 'store', 'update', 'destroy']])->names('myteam');
    Route::resource('/company/files', CompanyFilesController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('companyimages');
    
    /**
     * Projects
     */
    Route::resource('/projects', ProjectsController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('projects');
    Route::put('/projects/{project}/visible', [ProjectsControllerVisible::class, 'changevisible'])->name('projectsvisible');
    Route::resource('/projects/files', ProjectsFilesController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('projectsimages');
    /**
     * Products
     */
    Route::resource('/products', ProductsController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('products');
    Route::resource('/products/files', ProductsFilesController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('productsimages');
    Route::resource('/products/documents', ProductsDocumentsController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('productsdocuments');
    /**
     * brands
     */
    Route::resource('/brands', BrandsController::class, ['only' => ['index','show','store', 'edit', 'update', 'destroy']])->names('brands');
    /**
     * Tenders
     */
    Route::resource('/tenders', TendersController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('tenders');
    /**
     * Search
     */
    Route::get('/search/projects', SearchProjectsController::class)->name('search-projects');
    Route::get('/search/products', SearchProductsController::class)->name('search-products');
    // Route::get('/search/products', SearchProductsController::class)->name('search-products');
});
Route::post('/files', FilesController::class)->name('files');

Route::resource('/myaccount/registermember', RegisterMemberController::class, ['only' => ['store']])->names('registermember');

/**
 * User
 */
// Route::resource('user', 'user\UserController');