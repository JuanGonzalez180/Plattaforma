<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\blog\BlogController;
use App\Http\Controllers\ApiControllers\action\statistics\StatisticsController;
use App\Http\Controllers\ApiControllers\blog\BlogFilesController;
use App\Http\Controllers\ApiControllers\querywall\tenderQueryQuestionController;
use App\Http\Controllers\ApiControllers\querywall\tenderQueryAnswerController;
use App\Http\Controllers\ApiControllers\querywall\quotesQueryAnswerController;
use App\Http\Controllers\ApiControllers\portfolios\PortfoliosController;
use App\Http\Controllers\ApiControllers\catalogs\CatalogsControllers;
use App\Http\Controllers\ApiControllers\portfolios\PortfoliosDocumentsController;
use App\Http\Controllers\ApiControllers\catalogs\CatalogsDocumentsControllers;
use App\Http\Controllers\ApiControllers\brands\BrandsController;
use App\Http\Controllers\ApiControllers\category\CategoryController;
use App\Http\Controllers\ApiControllers\categoryservices\CategoryServicesController;
use App\Http\Controllers\ApiControllers\company\CompanyController;
use App\Http\Controllers\ApiControllers\company\CompanyProjects\CompanyProjectsController;
use App\Http\Controllers\ApiControllers\company\CompanyTenders\CompanyTendersController;
use App\Http\Controllers\ApiControllers\company\CompanyQuotes\CompanyQuotesController;
use App\Http\Controllers\ApiControllers\company\CompanyTenders\CompanyTendersTransactController;
use App\Http\Controllers\ApiControllers\company\CompanyQuotes\CompanyQuotesTransactController;
use App\Http\Controllers\ApiControllers\company\CompanyBlogs\CompanyBlogsController;
use App\Http\Controllers\ApiControllers\company\CompanyFileSize\CompanyFileSizeController;
use App\Http\Controllers\ApiControllers\company\CompanyProducts\CompanyProductsController;
use App\Http\Controllers\ApiControllers\company\CompanyRemarks\CompanyRemarksController;
use App\Http\Controllers\ApiControllers\company\CompanyTeams\CompanyTeamsController;
use App\Http\Controllers\ApiControllers\company\CompanyPortfolios\CompanyPortfoliosController;
use App\Http\Controllers\ApiControllers\company\CompanyCatalogs\CompanyCatalogsController;
use App\Http\Controllers\ApiControllers\company\CompanyFiles\CompanyFilesController;
use App\Http\Controllers\ApiControllers\country\CountryController;
use App\Http\Controllers\ApiControllers\files\FilesController;
use App\Http\Controllers\WebControllers\exportfile\xls\CategoriesFileController;
use App\Http\Controllers\ApiControllers\products\ProductsController;
use App\Http\Controllers\ApiControllers\products\ProductsDocumentsController;
use App\Http\Controllers\ApiControllers\products\ProductsFilesController;
use App\Http\Controllers\ApiControllers\projects\ProjectsController;
use App\Http\Controllers\ApiControllers\projects\ProjectsFilesController;
use App\Http\Controllers\ApiControllers\socialnetworks\SocialNetworksController;
use App\Http\Controllers\ApiControllers\staticcontent\StaticContentController;
use App\Http\Controllers\ApiControllers\tenders\TendersController;
use App\Http\Controllers\ApiControllers\quotes\QuotesController;
use App\Http\Controllers\ApiControllers\quotes\quotesCompanies\QuotesCompaniesController;
use App\Http\Controllers\ApiControllers\uploadfile\csv\productfile\ProductFileController;
use App\Http\Controllers\ApiControllers\tenders\tendersDocuments\TendersDocumentsController;
use App\Http\Controllers\ApiControllers\quotes\quotesDocuments\QuotesDocumentsController;
use App\Http\Controllers\ApiControllers\tenders\tendersCompanies\TendersCompaniesController;
use App\Http\Controllers\ApiControllers\tenders\tendersCompanies\TendersCompaniesDocumentsController;
use App\Http\Controllers\ApiControllers\quotes\quotesCompanies\QuotesCompaniesDocumentsController;
use App\Http\Controllers\ApiControllers\tenders\tendersCompanies\TendersCompaniesListController;
use App\Http\Controllers\ApiControllers\quotes\quotesCompanies\CuotesCompaniesListController;
use App\Http\Controllers\ApiControllers\tenders\tendersCompanies\TendersCompaniesActionController;
use App\Http\Controllers\ApiControllers\quotes\quotesCompanies\QuotesCompaniesActionController;
use App\Http\Controllers\ApiControllers\tenders\tendersAction\TendersActionController;
use App\Http\Controllers\ApiControllers\tenders\tendersVersions\TendersVersionsController;
use App\Http\Controllers\ApiControllers\quotes\quotesVersions\QuotesVersionsController;
use App\Http\Controllers\ApiControllers\typeproject\TypeProjectController;
use App\Http\Controllers\ApiControllers\typesentity\TypesEntityController;

// Search
use App\Http\Controllers\ApiControllers\search\SearchItemController;
use App\Http\Controllers\ApiControllers\search\SearchItemControllerOld;
use App\Http\Controllers\ApiControllers\search\SearchLikeItemController;
use App\Http\Controllers\ApiControllers\search\SearchLikeCompanyController;
use App\Http\Controllers\ApiControllers\search\SearchBrandsController;
use App\Http\Controllers\ApiControllers\search\SearchTendersController;
use App\Http\Controllers\ApiControllers\search\SearchCompanyController;
use App\Http\Controllers\ApiControllers\search\SearchProductsController;
use App\Http\Controllers\ApiControllers\search\SearchProjectsController;
// Random
use App\Http\Controllers\ApiControllers\random\RandomAdvertisingsController;
// Password
use App\Http\Controllers\ApiControllers\password\ChangePasswordController;
use App\Http\Controllers\ApiControllers\password\CodeValidationController;
use App\Http\Controllers\ApiControllers\password\SendCodeController;
// Account
use App\Http\Controllers\ApiControllers\myaccount\AccountChangePasswordController;
use App\Http\Controllers\ApiControllers\myaccount\AccountEditController;
use App\Http\Controllers\ApiControllers\myaccount\AccountMyCompanyController;
use App\Http\Controllers\ApiControllers\myaccount\AccountMyServicesController;
use App\Http\Controllers\ApiControllers\myaccount\AccountMyTeamController;
use App\Http\Controllers\ApiControllers\myaccount\RegisterMemberController;
// Subscriptions
use App\Http\Controllers\ApiControllers\stripe\SubscriptionsStripeController;
use App\Http\Controllers\ApiControllers\user\UsersController;
// Remarks
use App\Http\Controllers\ApiControllers\remarks\RemarksController;
// Interests or Favorites
use App\Http\Controllers\ApiControllers\interests\InterestsController;
use App\Http\Controllers\ApiControllers\favorites\FavoritesController;
// Notifications
use App\Http\Controllers\ApiControllers\notifications\UsersTokensController;
use App\Http\Controllers\ApiControllers\notifications\NotificationsController;
// Chat
use App\Http\Controllers\ApiControllers\chat\ChatController;
// Messages
use App\Http\Controllers\ApiControllers\messages\MessagesController;

// Advertising
use App\Http\Controllers\ApiControllers\publicity\advertising\AdvertisingController;
use App\Http\Controllers\ApiControllers\publicity\advertisingplanspaidimages\AdvertisingPlansPaidImagesController;
use App\Http\Controllers\ApiControllers\publicity\advertisingplans\AdvertisingPlansController;
// Company Changes
use App\Http\Controllers\ApiControllers\company\CompanyChanges\CompanyChangesNameController;
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

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('user', [UsersController::class, 'getAuthenticatedUser'])->name('user');
    /**
     * My Account
     */
    Route::resource('/myaccount/changepassword', AccountChangePasswordController::class, ['only' => ['store']])->names('changepasswordaccount');
    Route::resource('/myaccount/accountedit', AccountEditController::class, ['only' => ['store']])->names('accountedit');
    Route::get('/myaccount/mycompany', AccountMyCompanyController::class)->name('mycompany');
    Route::resource('/myaccount/mycompany', AccountMyCompanyController::class, ['only' => ['store']])->names('mycompany');
    Route::resource('/myaccount/myservices', AccountMyServicesController::class, ['only' => ['index', 'store']])->names('myservices');
    Route::resource('/myaccount/myteam', AccountMyTeamController::class, ['only' => ['index', 'store', 'update', 'destroy']])->names('myteam');
    Route::get('/myaccount/myteam/approved', [AccountMyTeamController::class, 'teamUsersApproved'])->name('myteamapproved');
    Route::get('/myaccount/myteam/resend/invitation/{team_id}', [AccountMyTeamController::class, 'resendInvitation'])->name('resentinvitation');
    Route::resource('/company/files', CompanyFilesController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('companyimages');
    Route::resource('/company/name', CompanyChangesNameController::class, ['only' => ['store']])->names('companychangename');
    /**
     * Projects
     */
    Route::resource('/projects', ProjectsController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('projects');
    Route::put('/projects/{project}/visible', [ProjectsController::class, 'changevisible'])->name('projectsvisible');
    Route::resource('/projects/files', ProjectsFilesController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('projectsimages');
    Route::get('/projects/all', [ProjectsController::class, 'all'])->name('project-list-all');
    /**
     * Products
     */
    Route::resource('/statistics', StatisticsController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('statistics');
    /**
     * Products
     */
    Route::resource('/products', ProductsController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('products');
    Route::resource('/products/files', ProductsFilesController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('productsimages');
    Route::resource('/products/documents', ProductsDocumentsController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('productsdocuments');
    /**
     * brands
     */
    Route::resource('/brands', BrandsController::class, ['only' => ['index', 'show', 'store', 'edit', 'update', 'destroy']])->names('brands');
    /**
     * blogs
     */
    Route::resource('/blogs/files', BlogFilesController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('blogsimages');
    Route::resource('/blogs', BlogController::class, ['only' => ['index', 'show', 'store', 'edit', 'update', 'destroy']])->names('blogs');
    /**
     * pruebas
     */
    // Route::resource('/search/items', SearchItemController::class, ['only' => ['index']])->names('search-items');
    /**
     * Query_wall
     */
    Route::resource('/querywall/tenders/question', tenderQueryQuestionController::class, ['only' => ['index', 'show', 'store', 'edit', 'update', 'destroy']])->names('querywalltendersQuestions');
    Route::resource('/querywall/tenders/answer', tenderQueryAnswerController::class, ['only' => ['index', 'show', 'store', 'edit', 'update', 'destroy']])->names('querywalltendersAnswer');
    Route::resource('/querywall/quotes/answer', quotesQueryAnswerController::class, ['only' => ['index', 'show', 'store', 'edit', 'update', 'destroy']])->names('querywallquotesAnswer');
    Route::put('/querywall/{id}/visible', [tenderQueryAnswerController::class, 'changevisible'])->name('querywallvisible');
    /**
     * portfolios
     */
    Route::resource('/portfolios', PortfoliosController::class, ['only' => ['index', 'show', 'store', 'edit', 'update', 'destroy']])->names('portfolios');
    Route::resource('/portfolios/documents', PortfoliosDocumentsController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('portfoliosdocuments');
    /**
     * catalogs
     */
    Route::resource('/catalogs', CatalogsControllers::class, ['only' => ['index', 'show', 'store', 'edit', 'update', 'destroy']])->names('catalogs');
    Route::resource('/catalogs/documents', CatalogsDocumentsControllers::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('catalogsdocuments');
    /**
     * Tenders_docuemnts
     */
    Route::resource('/tenders/documents', TendersDocumentsController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('tendersdocuments');
    /**
     * quotes_docuemnts
     */
    Route::resource('/quotes/documents', QuotesDocumentsController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('quotesdocuments');
    /**
     * Tenders_companies_docuemnts
     */
    Route::resource('/tenders/companies/documents', TendersCompaniesDocumentsController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('tenderscompaniesdocuments');
    /**
     * quotes_companies_docuemnts
     */
    Route::resource('/quotes/companies/documents', QuotesCompaniesDocumentsController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('quotescompaniesdocuments');
    /**
     * quotes_companies_docuemnts
     */
    /**
     * Tenders_vesion
     */
    Route::resource('/tenders/version', TendersVersionsController::class, ['only' => ['index', 'store', 'show', 'edit', 'update', 'destroy']])->names('tendersVersions');
    /**
     * quotes_vesion
     */
    Route::resource('/quotes/version', QuotesVersionsController::class, ['only' => ['index', 'store', 'show', 'edit', 'update', 'destroy']])->names('quotesVersions');
    /**
     * Tenders_companies
     */
    Route::resource('/tenders/companies', TendersCompaniesController::class, ['only' => ['index', 'store', 'show', 'edit', 'update', 'destroy']])->names('tendersCompanies');
    Route::resource('/quotes/companies', QuotesCompaniesController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('quotescompanies');
    Route::get('/tenders/all/companies', [TendersCompaniesListController::class, 'indexTendersCompanies'])->name('company-tender-list');
    Route::get('/quotes/all/companies', [CuotesCompaniesListController::class, 'indexQuotesCompanies'])->name('company-quote-list');
    Route::get('/tenders/companies/selected/winner', [TendersCompaniesActionController::class, 'SelectedWinner'])->name('company-company-selected-winner');
    Route::get('/tenders/companies/desert/tender', [TendersCompaniesActionController::class, 'desertTender'])->name('company-company-desert-tender');
    Route::get('/tenders/companies/selected/more/winner', [TendersCompaniesActionController::class, 'SelectedMoreWinner'])->name('company-company-selected-more--winner');
    /**
     * quotes_action
     */
    Route::get('/quotes/companies/selected/winner', [QuotesCompaniesActionController::class, 'SelectedWinner'])->name('company-quotes-selected-winner');
    /**
     * Tenders_action
     */
    Route::put('/tenders/action/{id}/update/user', [TendersActionController::class, 'updateTenderUser'])->name('company-tender-update-user');
    Route::put('/tenders/action/{id}/closed/status', [TendersActionController::class, 'updateStatusClosed'])->name('company-tender-update-status-closed');
    Route::put('/tenders/action/{id}/declined/status', [TendersActionController::class, 'updateStatusDeclined'])->name('company-tender-update-status-declined');
    Route::put('/tenders/action/{id}/disabled/status', [TendersActionController::class, 'updateStatusDisabled'])->name('company-tender-update-status-disabled');
    Route::put('/tenders/action/{id}/enabled/status', [TendersActionController::class, 'updateStatusEnabled'])->name('company-tender-update-status-enabled');
    /**
     * Tenders
     */
    Route::resource('/tenders', TendersController::class, ['only' => ['index', 'store', 'show', 'edit', 'update', 'destroy']])->names('tenders');
    Route::get('/tender/type/all', [TendersController::class, 'tenderTypeAll'])->name('tender-type-all');
    /**
     * quotes
     */
    Route::resource('/quotes', QuotesController::class, ['only' => ['index', 'store', 'show', 'edit', 'update', 'destroy']])->names('quotes');
    // Route::get('/tender/type/all', [TendersController::class, 'tenderTypeAll'])->name('tender-type-all');
    /**
     * Company
     */
    Route::get('/company/status', [CompanyController::class, 'statusCompany'])->name('company-status');
    Route::get('/company/{slug}', [CompanyController::class, 'show'])->name('company-show');
    Route::post('/company/item/update', [CompanyController::class, 'updateItem'])->name('company-item-update');

    Route::get('/company/{slug}/detail', [CompanyController::class, 'detail'])->name('company-detail');

    Route::get('/company/{slug}/projects', [CompanyProjectsController::class, 'index'])->name('company-projects');
    Route::get('/company/{slug}/project/{id}', [CompanyProjectsController::class, 'show'])->name('company-detail-project');

    Route::get('/company/{slug}/tenders', [CompanyTendersController::class, 'index'])->name('company-tenders');
    Route::get('/company/{slug}/tenders/{id}', [CompanyTendersController::class, 'show'])->name('company-tender-detail');
    Route::get('/company/tenders/{id}/edit', [CompanyTendersController::class, 'edit'])->name('company-tender-edit');
    Route::put('/company/{slug}/tenders/{id}', [CompanyTendersController::class, 'update'])->name('company-tender-update');
    Route::delete('/company/{slug}/tenders/{id}', [CompanyTendersController::class, 'destroy'])->name('company-tender-destroy');
    
    
    
    
    
    
    
    Route::get('/company/quotes/{id}/edit', [CompanyQuotesController::class, 'edit'])->name('company-quote-edit');
    Route::put('/company/{slug}/quotes/{id}', [CompanyQuotesController::class, 'update'])->name('company-quote-update');





    Route::get('/company/{slug}/quotes', [CompanyQuotesController::class, 'index'])->name('company-quotes');
    Route::get('/company/{slug}/quotes/{id}', [CompanyQuotesController::class, 'show'])->name('company-quotes-detail');
    // Route::get('/company/tenders/{id}/edit', [CompanyTendersController::class, 'edit'])->name('company-tender-edit');
    // Route::put('/company/{slug}/tenders/{id}', [CompanyTendersController::class, 'update'])->name('company-tender-update');
    // Route::delete('/company/{slug}/tenders/{id}', [CompanyTendersController::class, 'destroy'])->name('company-tender-destroy');
 
    // participar en cotización:
    Route::post('/company/quotes/select/participate', [CompanyQuotesTransactController::class, 'postComparate'])->name('company-quotes-select-participate');
    Route::put('/company/{slug}/quotes/{id}/status/{status}/user/{user_id}', [CompanyQuotesController::class, 'updateStatusInvitation'])->name('company-quote-update-status'); 

    //participar en licitación
    Route::put('/company/{slug}/tenders/{id}/status/{status}/user/{user_id}', [CompanyTendersController::class, 'updateStatusInvitation'])->name('company-tender-update-status'); 
    Route::post('/company/{slug}/tenders/{id}/send/participate', [CompanyTendersTransactController::class, 'store'])->name('company-send-participate');
    Route::post('/company/tenders/select/participate', [CompanyTendersTransactController::class, 'postComparate'])->name('company-select-participate');

    Route::get('/company/{slug}/blogs', [CompanyBlogsController::class, 'index'])->name('company-blogs');
    Route::get('/company/{slug}/blogs/{id}', [CompanyBlogsController::class, 'show'])->name('company-detail-blogs');

    Route::get('/company/{slug}/products', [CompanyProductsController::class, 'index'])->name('company-products');
    Route::get('/company/{slug}/products/{id}', [CompanyProductsController::class, 'show'])->name('company-detail-products');

    Route::get('/company/{slug}/teams', [CompanyTeamsController::class, 'index'])->name('company-teams');
    Route::get('/company/{slug}/teams/admin', [CompanyTeamsController::class, 'getAdminCompany'])->name('company-admin-teams');
    Route::get('/company/{slug}/portfolios', [CompanyPortfoliosController::class, 'index'])->name('company-portfolios');
    Route::get('/company/{slug}/catalogs', [CompanyCatalogsController::class, 'index'])->name('company-catalog');

    Route::get('/company/{slug}/remarks', [CompanyRemarksController::class, 'index'])->name('company-remarks');

    Route::get('/company/files/size', [CompanyFileSizeController::class, 'index'])->name('company-files-size');

    //subir archivos
    /*csv*/
    Route::post('/uploadfile/csv/product/file', [ProductFileController::class, 'store'])->name('csv-product-file');
    Route::get('/filedownload/csv/product/file', [ProductFileController::class, 'downloadTemplate'])->name('download-csv-product-file');
    /**
     * Search
     */
    Route::post('/search/projects', SearchProjectsController::class)->name('search-projects');
    Route::post('/search/products', SearchProductsController::class)->name('search-products');
    Route::post('/search/brands', SearchBrandsController::class)->name('search-brands');
    Route::post('/search/tenders', SearchTendersController::class)->name('search-tenders');
    Route::post('/search/companies', SearchCompanyController::class)->name('search-companies');
    // Route::post('/search/item/companies', SearchLikeCompanyController::class)->name('search-item-companies');
    Route::resource('/search/items', SearchItemControllerOld::class, ['only' => ['index']])->names('search-items');
    Route::resource('/search/like/items', SearchLikeItemController::class, ['only' => ['index']])->names('search-like-items');
    /**
     * Random
     */
    Route::post('/advertisings/random', RandomAdvertisingsController::class)->name('advertisings-random');
    // Route::resource('/search/items/parameters', SearchParameterController::class, ['only' => ['index']])->names('search-parameter');
    Route::post('/search/items/parameters', SearchItemController::class)->name('search-parameter');
    // Route::get('/search/products', SearchProductsController::class)->name('search-products');
    // Remarks
    Route::resource('/remarks', RemarksController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('remarks');
    // Interests or Favorites
    Route::resource('/interests', InterestsController::class, ['only' => ['index', 'store', 'destroy']])->names('interests');
    Route::resource('/favorites', FavoritesController::class, ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->names('favorites');
    // Notifications 
    Route::resource('/tokens', UsersTokensController::class, ['only' => ['store']])->names('tokens');
    Route::resource('/notifications', NotificationsController::class, ['only' => ['index', 'destroy','update']])->names('notifications');
    // Chat
    Route::resource('/chats', ChatController::class, ['only' => ['index', 'store']])->names('chats');
    Route::get('/chats/notread', [ChatController::class, 'notread'])->name('chats-notread');
    // Messages
    Route::resource('/messages', MessagesController::class, ['only' => ['index', 'store']])->names('messages');
    Route::resource('advertisings/plans/images', AdvertisingPlansPaidImagesController::class, ['only' => ['index', 'edit', 'update']])->names('advertisings_images');
    Route::get('/advertisings/plans', AdvertisingPlansController::class)->name('advertisings_plans');
    Route::resource('/advertisings', AdvertisingController::class, ['only' => ['index', 'store', 'edit', 'update']])->names('advertisings');
    //EXPORT FILES
    Route::get('/categories/list/file/xlsx', function () {
        return (new CategoriesFileController)->export()->download('categorias.xlsx');
    })->name('cotegory-export-api');
});
// Route::post('/files', FilesController::class)->name('files');

Route::resource('/myaccount/registermember', RegisterMemberController::class, ['only' => ['store']])->names('registermember');

Route::post('/tokens/test', [UsersTokensController::class, 'sendWebNotification'])->name('test-notification');

/**
 * User
 */
// Route::resource('user', 'user\UserController');