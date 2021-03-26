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
use ApiControllers\projects\ProjectsController;
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
 * Addresses
 */
// Route::resource('addresses', 'addresses\AddressesController');

/**
 * Blog
 */
// Route::resource('blog', 'blog\BlogController');

/**
 * Category
 */
// Route::resource('categories', 'category\CategoryController');

/**
 * Chat
 */
// Route::resource('chat', 'chat\ChatController');

/**
 * Countries
 */
// Route::resource('countries', 'countries\CountriesController');

/**
 * Files
 */
// Route::resource('files', 'files\FilesController');

/**
 * Interests
 */
// Route::resource('interests', 'interests\InterestsController');

/**
 * Messages
 */
// Route::resource('messages', 'messages\MessagesController');

/**
 * MetaData
 */
// Route::resource('metadata', 'metadata\MetaDataController');

/**
 * Products
 */
// Route::resource('products', 'products\ProductsController');

/**
 * Proponents
 */
// Route::resource('proponents', 'proponents\ProponentsController');

/**
 * QueryWall
 */
// Route::resource('querywall', 'querywall\QueryWallController');

/**
 * Remarks
 */
// Route::resource('remarks', 'remarks\RemarksController');

/**
 * SocialNetworks
 */
// Route::resource('socialnetworks', 'socialnetworks\SocialNetworksController');

/**
 * Tags
 */
// Route::resource('tags', 'tags\TagsController');

/**
 * Tenders
 */
// Route::resource('tenders', 'tenders\TendersController');

/**
 * TendersVersions
 */
// Route::resource('tendersversions', 'tendersversions\TendersVersionsController');

/**
 * TypeProject
 */
// Route::resource('typeproject', 'typeproject\TypeProjectController');

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

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('user',[UsersController::class, 'getAuthenticatedUser'])->name('user');

    /**
     * My Account
     */
    Route::resource('/myaccount/changepassword', AccountChangePasswordController::class, ['only' => ['store']])->names('changepasswordaccount');
    Route::resource('/myaccount/accountedit', AccountEditController::class, ['only' => ['store']])->names('accountedit');
    Route::get('/myaccount/mycompany', AccountMyCompanyController::class)->name('mycompany');
    Route::resource('/myaccount/mycompany', AccountMyCompanyController::class, ['only' => ['store']])->names('mycompany');
    Route::resource('/myaccount/sendinvitation', SendInvitationController::class, ['only' => ['store']])->names('sendinvitation');
    Route::resource('/myaccount/myteam', AccountMyTeamController::class, ['only' => ['index', 'store', 'update', 'destroy']])->names('myteam');
    Route::resource('/projects', ProjectsController::class, ['only' => ['store']])->names('projects');
});

Route::resource('/myaccount/registermember', RegisterMemberController::class, ['only' => ['store']])->names('registermember');

/**
 * User
 */
// Route::resource('user', 'user\UserController');