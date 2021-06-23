<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\blog\BlogController;
use App\Http\Controllers\ApiControllers\blog\BlogFilesController;
use App\Http\Controllers\ApiControllers\querywall\tenderQueryQuestionController;
use App\Http\Controllers\ApiControllers\querywall\tenderQueryAnswerController;
use App\Http\Controllers\ApiControllers\portfolios\PortfoliosController;
use App\Http\Controllers\ApiControllers\portfolios\PortfoliosDocumentsController;
use App\Http\Controllers\ApiControllers\brands\BrandsController;
use App\Http\Controllers\ApiControllers\category\CategoryController;
use App\Http\Controllers\ApiControllers\category\CategoryListController;
use App\Http\Controllers\ApiControllers\categoryservices\CategoryServicesController;
use App\Http\Controllers\ApiControllers\company\CompanyController;
use App\Http\Controllers\ApiControllers\company\CompanyProjects\CompanyProjectsController;
use App\Http\Controllers\ApiControllers\company\CompanyTenders\CompanyTendersController;
use App\Http\Controllers\ApiControllers\company\CompanyTenders\CompanyTendersTransactController;
use App\Http\Controllers\ApiControllers\company\CompanyBlogs\CompanyBlogsController;
use App\Http\Controllers\ApiControllers\company\CompanyProducts\CompanyProductsController;
use App\Http\Controllers\ApiControllers\company\CompanyFiles\CompanyFilesController;
use App\Http\Controllers\ApiControllers\country\CountryController;
use App\Http\Controllers\ApiControllers\files\FilesController;
use App\Http\Controllers\ApiControllers\products\ProductsController;
use App\Http\Controllers\ApiControllers\products\ProductsDocumentsController;
use App\Http\Controllers\ApiControllers\products\ProductsFilesController;
use App\Http\Controllers\ApiControllers\projects\ProjectsController;
use App\Http\Controllers\ApiControllers\projects\ProjectsFilesController;
use App\Http\Controllers\ApiControllers\socialnetworks\SocialNetworksController;
use App\Http\Controllers\ApiControllers\staticcontent\StaticContentController;
use App\Http\Controllers\ApiControllers\tenders\TendersController;
use App\Http\Controllers\ApiControllers\tenders\tendersDocuments\TendersDocumentsController;
use App\Http\Controllers\ApiControllers\tenders\tendersCompanies\TendersCompaniesController;
use App\Http\Controllers\ApiControllers\tenders\tendersCompanies\TendersCompaniesDocumentsController;
use App\Http\Controllers\ApiControllers\tenders\tendersCompanies\TendersCompaniesListController;
use App\Http\Controllers\ApiControllers\tenders\tendersCompanies\TendersCompaniesActionController;
use App\Http\Controllers\ApiControllers\tenders\tendersAction\TendersActionController;
use App\Http\Controllers\ApiControllers\tenders\tendersVersions\TendersVersionsController;
use App\Http\Controllers\ApiControllers\typeproject\TypeProjectController;
use App\Http\Controllers\ApiControllers\typeproject\TypeProjectListController;
use App\Http\Controllers\ApiControllers\typesentity\TypesEntityController;
// Search
use App\Http\Controllers\ApiControllers\search\SearchBrandsController;
use App\Http\Controllers\ApiControllers\search\SearchCompanyController;
use App\Http\Controllers\ApiControllers\search\SearchProductsController;
use App\Http\Controllers\ApiControllers\search\SearchProjectsController;
// Password
use App\Http\Controllers\ApiControllers\password\ChangePasswordController;
use App\Http\Controllers\ApiControllers\password\CodeValidationController;
use App\Http\Controllers\ApiControllers\password\SendCodeController;
// Account
use App\Http\Controllers\ApiControllers\myaccount\AccountChangePasswordController;
use App\Http\Controllers\ApiControllers\myaccount\AccountEditController;
use App\Http\Controllers\ApiControllers\myaccount\AccountMyCompanyController;
use App\Http\Controllers\ApiControllers\myaccount\AccountMyTeamController;
use App\Http\Controllers\ApiControllers\myaccount\RegisterMemberController;
// Subscriptions
use App\Http\Controllers\ApiControllers\stripe\SubscriptionsStripeController;
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
 * TypeProjects
 */
Route::get('/typesprojects/list/item/child', [TypeProjectListController::class, 'index'])->name('typesprojects-items-child');
/**
 * Categories
 */
Route::get('/categories', CategoryController::class)->name('categories');
Route::get('/categories/list/item/child', [CategoryListController::class, 'index'])->name('categories-items-child');
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
    Route::put('/projects/{project}/visible', [ProjectsController::class, 'changevisible'])->name('projectsvisible');
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
     * blogs
     */
    Route::resource('/blogs/files', BlogFilesController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('blogsimages');
    Route::resource('/blogs', BlogController::class, ['only' => ['index','show','store', 'edit', 'update', 'destroy']])->names('blogs');
    /**
     * Query_wall
     */
    Route::resource('/querywall/tenders/question', tenderQueryQuestionController::class, ['only' => ['index','show','store', 'edit', 'update', 'destroy']])->names('querywalltendersQuestions');
    Route::resource('/querywall/tenders/answer', tenderQueryAnswerController::class, ['only' => ['index','show','store', 'edit', 'update', 'destroy']])->names('querywalltendersAnswer');
    Route::put('/querywall/{id}/visible', [tenderQueryAnswerController::class, 'changevisible'])->name('querywallvisible');

    /**
     * portfolios
     */
    Route::resource('/portfolios', PortfoliosController::class, ['only' => ['index','show','store', 'edit', 'update', 'destroy']])->names('portfolios');
    Route::resource('/portfolios/documents', PortfoliosDocumentsController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('portfoliosdocuments');
    /**
     * Tenders_docuemnts
     */
    Route::resource('/tenders/documents', TendersDocumentsController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('tendersdocuments');
    /**
     * Tenders_companies_docuemnts
     */
    Route::resource('/tenders/companies/documents', TendersCompaniesDocumentsController::class, ['only' => ['index','store', 'edit', 'update', 'destroy']])->names('tenderscompaniesdocuments');
    /**
     * Tenders_vesion
     */
    Route::resource('/tenders/version', TendersVersionsController::class, ['only' => ['index','store', 'show', 'edit', 'update', 'destroy']])->names('tendersVersions');
    /**
     * Tenders_companies
     */
    Route::resource('/tenders/companies', TendersCompaniesController::class, ['only' => ['index','store', 'show', 'edit', 'update', 'destroy']])->names('tendersCompanies');
    Route::get('/tenders/all/companies', [TendersCompaniesListController::class, 'indexTendersCompanies'])->name('company-tender-list');
    Route::get('/tenders/companies/selected/winner', [TendersCompaniesActionController::class, 'SelectedWinner'])->name('company-company-selected-winner');

    /**
     * Tenders_action
     */    
    Route::put('/tenders/action/{id}/update/user', [TendersActionController::class, 'updateTenderUser'])->name('company-tender-update-user');
    Route::put('/tenders/action/{id}/closed/status', [TendersActionController::class, 'updateStatusClosed'])->name('company-tender-update-status-closed');
    /**
     * Tenders
     */
    Route::resource('/tenders', TendersController::class, ['only' => ['index','store', 'show', 'edit', 'update', 'destroy']])->names('tenders');
    
    /**
     * Company
     */
    Route::get('/company/{slug}', [CompanyController::class, 'show'])->name('company-show');

    Route::get('/company/{slug}/detail', [CompanyController::class, 'detail'])->name('company-detail');

    Route::get('/company/{slug}/projects', [CompanyProjectsController::class, 'index'])->name('company-projects');
    Route::get('/company/{slug}/project/{id}', [CompanyProjectsController::class, 'show'])->name('company-detail-project');

    Route::get('/company/{slug}/tenders', [CompanyTendersController::class, 'index'])->name('company-tenders');
    Route::get('/company/{slug}/tenders/{id}', [CompanyTendersController::class, 'show'])->name('company-tender-detail');
    Route::put('/company/{slug}/tenders/{id}', [CompanyTendersController::class, 'update'])->name('company-tender-update');
    Route::delete('/company/{slug}/tenders/{id}', [CompanyTendersController::class, 'destroy'])->name('company-tender-destroy');
    //participar en licitación
    Route::post('/company/{slug}/tenders/{id}/send/participate', [CompanyTendersTransactController::class, 'store'])->name('company-send-participate');
    Route::get('/company/{slug}/tenders/select/participate', [CompanyTendersTransactController::class, 'index'])->name('company-select-participate');

    Route::get('/company/{slug}/blogs', [CompanyBlogsController::class, 'index'])->name('company-blogs');
    Route::get('/company/{slug}/blogs/{id}', [CompanyBlogsController::class, 'show'])->name('company-detail-blogs');

    Route::get('/company/{slug}/products', [CompanyProductsController::class, 'index'])->name('company-products');
    Route::get('/company/{slug}/products/{id}', [CompanyProductsController::class, 'show'])->name('company-detail-products');

    /**
     * Search
     */
    Route::get('/search/projects', SearchProjectsController::class)->name('search-projects');
    Route::get('/search/products', SearchProductsController::class)->name('search-products');
    Route::post('/search/brands', SearchBrandsController::class)->name('search-brands');
    Route::post('/search/companies', SearchCompanyController::class)->name('search-companies');
    // Route::get('/search/products', SearchProductsController::class)->name('search-products');
});
// Route::post('/files', FilesController::class)->name('files');

Route::resource('/myaccount/registermember', RegisterMemberController::class, ['only' => ['store']])->names('registermember');

/**
 * User
 */
// Route::resource('user', 'user\UserController');