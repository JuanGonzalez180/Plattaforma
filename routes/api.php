<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
 * Company
 */
// Route::resource('companies', 'company\CompanyController', ['only' => ['index','create','show','edit','update']]);

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
// Route::resource('typesentity', 'typesentity\TypesEntityController');

/**
 * User
 */
// Route::resource('user', 'user\UserController');