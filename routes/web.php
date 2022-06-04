<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebControllers\HomeController;
use App\Http\Controllers\WebControllers\team\TeamController;
use App\Http\Controllers\WebControllers\test\TestController;
use App\Http\Controllers\WebControllers\blog\BlogController;
use App\Http\Controllers\WebControllers\user\UsersController;
use App\Http\Controllers\WebControllers\stripe\PlanController;
use App\Http\Controllers\WebControllers\remark\RemarkController;
use App\Http\Controllers\WebControllers\brands\BrandsController;
use App\Http\Controllers\WebControllers\tender\TenderController;
use App\Http\Controllers\WebControllers\tenderemailcompanies\TenderEmailCompaniesController;


use App\Http\Controllers\WebControllers\company\CompanyController;
use App\Http\Controllers\WebControllers\scripts\RemoveUnwantedUsersController;
use App\Http\Controllers\WebControllers\company\projects\CompanyProjectController;


use App\Http\Controllers\WebControllers\company\CompanyFiles\CompanyFilesController;

use App\Http\Controllers\WebControllers\exportfile\xls\CategoriesFileController;

use App\Http\Controllers\WebControllers\company\providers\CompanyProvidersController;
use App\Http\Controllers\WebControllers\project\ProjectController;
use App\Http\Controllers\WebControllers\product\ProductController;
use App\Http\Controllers\WebControllers\catalog\CatalogsController;
use App\Http\Controllers\WebControllers\country\CountryController;
use App\Http\Controllers\WebControllers\category\CategoryController;
use App\Http\Controllers\WebControllers\querywall\QueryWallController;
use App\Http\Controllers\WebControllers\stripe\SubscriptionController;
use App\Http\Controllers\WebControllers\portfolio\PortfolioController;
use App\Http\Controllers\WebControllers\stripe\ProductsStripeController;
use App\Http\Controllers\WebControllers\typeproject\TypeProjectController;
use App\Http\Controllers\WebControllers\scripts\ScriptFilesController;
use App\Http\Controllers\WebControllers\typesentity\TypesEntityController;
use App\Http\Controllers\WebControllers\staticcontent\StaticContentController;
use App\Http\Controllers\WebControllers\socialnetworks\SocialNetworksController;
use App\Http\Controllers\WebControllers\tendercompanies\TenderCompaniesController;
use App\Http\Controllers\WebControllers\uploadfile\template\ProductFileController;
use App\Http\Controllers\WebControllers\uploadfile\file\importCategoriesController;
use App\Http\Controllers\WebControllers\categoryservices\CategoryServicesController;
use App\Http\Controllers\WebControllers\company\CompanyDelete\CompanyDeleteController;
use App\Http\Controllers\WebControllers\publicity\advertisingplans\AdvertisingController;
use App\Http\Controllers\WebControllers\publicity\manageadvertising\ManageAdvertisingController;
use App\Http\Controllers\WebControllers\publicity\imagesadvertisingplan\ImagesAdvertisingPlansController;

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

//scripts
Route::get('script/update/generic', [ScriptFilesController::class, 'genericScript']);

Route::get('script/delete/filesize', [ScriptFilesController::class, 'deleteFileNotExist']);

Route::get('script/delete/users/unwanted', RemoveUnwantedUsersController::class)->name('users-delete-unwanted');



Route::group(['middleware' => 'auth'], function () {
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

        // Muro de consultas
        Route::get('/querywall/{id}', [QueryWallController::class, 'index'])->name('query.class.id');

        Route::post('/querywall/edit/visible', [QueryWallController::class, 'editVisible'])
                ->name('querywall.edit.visible');

        //reseñas
        Route::get('/remark/{class}/{id}', [RemarkController::class, 'index'])->name('remark.class.id');

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

        Route::post('/typesentity/type', [TypesEntityController::class, 'getTypeEntityType'])
                ->name('typesentity.type');

        // Compañias
        Route::resource('companias', CompanyController::class)
                ->names('companies')
                ->parameters(['companias' => 'companies']);

        Route::post('companies/edit/status', [CompanyController::class, 'editStatus'])
                ->name('company.edit.status');

        Route::post('/companias/type', [CompanyController::class, 'getTypeCompanies'])
                ->name('companies.type');

        Route::get('/company/{type}', [CompanyController::class, 'getCompanyType'])->name('companies-type');

        Route::get('/company/delete/{id}', CompanyDeleteController::class)->name('company-delete');
        // ------------------------------------------------------------------------------------------------------------------
        Route::get('/company/all/projects', [CompanyProjectController::class, 'index'])->name('companies-all-projects');
        Route::post('/company/get/projects', [CompanyProjectController::class, 'getCompany'])->name('companies-get-projects');
        Route::post('/company/get/projects/status', [CompanyProjectController::class, 'getCountStatus'])->name('companies-status-projects');
        
        Route::post('/company/get/files', [CompanyFilesController::class, 'getFiles'])->name('companies-get-files');
        
        Route::get('/company/all/providers', [CompanyProvidersController::class, 'index'])->name('companies-all-providers');
        Route::post('/company/get/providers', [CompanyProvidersController::class, 'getCompany'])->name('companies-get-providers');
        Route::post('/company/get/providers/status', [CompanyProvidersController::class, 'getCountStatus'])->name('companies-status-providers');
        
        // Licitaciones
        Route::resource('licitaciones', TenderController::class, ['only' => ['edit', 'show']])
                ->names('tender')
                ->parameters(['licitaciones' => 'tender']);

        Route::post('/tenders/all/companies', [TenderController::class, 'getTenders'])->name('tenders.companies.all');

        Route::post('/tenders/all/invitation/emails', [TenderEmailCompaniesController::class, 'getTendersInvitation'])->name('tenders.invitation.email.all');

        Route::get('/tender/companies/emails', [TenderEmailCompaniesController::class, 'getFullCompanyTendersEmails'])->name('tenders-emails-companies-all');
        
        Route::get('/tender/all', [TenderController::class, 'getFullTenders'])->name('tenders-companies-all');
        Route::get('/tender/{type}/{id}', [TenderController::class, 'index'])->name('tender-company-id');
        


        Route::post('/tender/decline', [TenderController::class, 'updateStatusDecline'])
                ->name('tender.decline');

        // Teams - Equipo 
        Route::get('/teams/company/{id}', [TeamController::class, 'index'])->name('teams-company-id');

        Route::resource('teams', TeamController::class, ['only' => ['edit', 'show']])
                ->names('team')
                ->parameters(['teams' => 'team']);

        Route::post('teams/edit/status', [TeamController::class, 'editStatus'])
                ->name('teams.edit.status');


        // Compañias licitantes
        Route::get('/tendercompanies/{id}', [TenderCompaniesController::class, 'index'])->name('tender-companies-id');

        Route::resource('tender/companies/detail', TenderCompaniesController::class, ['only' => ['show']])
                ->names('tender-companies')
                ->parameters(['tendercompanies' => 'tender']);

        Route::PUT('/tendercompanies', [TenderCompaniesController::class, 'update'])->name('tender-companies-update');


        // Productos/Servicios
        Route::get('/company/product/{id}', [ProductController::class, 'index'])->name('product-company-id');

        Route::PUT('/product', [ProductController::class, 'update'])->name('product-update');

        Route::resource('product', ProductController::class, ['only' => ['edit', 'show']])
                ->names('productos')
                ->parameters(['product' => 'productos']);

        Route::post('/company/products', [ProductController::class, 'getCompanyProducts'])
                ->name('company.products');

        //catalogs
        Route::get('/company/catalog/{id}', [CatalogsController::class, 'index'])->name('catalog.company.id');

        Route::post('/company/catalogs', [CatalogsController::class, 'getCompanyCatalogs'])
                ->name('company.catalogs');

        Route::resource('catalogs', CatalogsController::class, ['only' => ['edit', 'show']])
                ->names('catalog.detail')
                ->parameters(['catalog' => 'catalogs']);

        // Proyectos
        Route::resource('proyecto', ProjectController::class, ['only' => ['edit', 'show']])
                ->names('project')
                ->parameters(['proyecto' => 'project']);

        Route::PUT('/project', [ProjectController::class, 'update'])->name('project-companies-update');

        Route::get('/project/company/{id}', [ProjectController::class, 'index'])->name('project-company-id');

        Route::post('/project/edit/visible', [ProjectController::class, 'editVisible'])
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
        Route::resource('blog', BlogController::class, ['only' => ['edit', 'show']])
                ->names('blog')
                ->parameters(['blog' => 'blog']);

        Route::PUT('/blog', [BlogController::class, 'update'])->name('blog-update');

        Route::get('/blog/company/{id}', [BlogController::class, 'index'])
                ->name('blog.company.id');

        // portafolio
        Route::resource('portfolio', PortfolioController::class, ['only' => ['edit', 'show']])
                ->names('portfolio')
                ->parameters(['portfolio' => 'portfolio']);

        Route::PUT('/portfolio', [PortfolioController::class, 'update'])->name('portfolio-update');

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

        //publicidad
        Route::resource('publicity_plan', AdvertisingController::class, ['only' => ['index', 'edit', 'update', 'show', 'store', 'create', 'destroy']])
                ->names('publicity_plan')
                ->parameters(['img_publicity_plan' => 'img_publicity_plan']);

        Route::resource('img_publicity_plan', ImagesAdvertisingPlansController::class, ['only' => ['index', 'edit', 'update', 'show', 'store', 'create', 'destroy']])
                ->names('img_publicity_plan')
                ->parameters(['img_publicity_plan' => 'img_publicity_plan']);

        Route::resource('manage_publicity_plan', ManageAdvertisingController::class, ['only' => ['index', 'edit', 'update', 'show', 'store', 'create', 'destroy']])
                ->names('manage_publicity_plan')
                ->parameters(['manage_publicity_plan' => 'manage_publicity_plan']);

        Route::post('/manage_publicity_plan/company', [ManageAdvertisingController::class, 'getAdvertisingCompanies'])
                ->name('manage_publicity_plan.company');

        Route::post('/manage_publicity_plan/update/status', [ManageAdvertisingController::class, 'update_status'])->name('adver-status-update');
        // tests
        Route::resource('test', TestController::class, ['only' => ['index', 'edit', 'update', 'show', 'store']])
                ->names('testing')
                ->parameters(['test' => 'test']);

        //UPLOAD FILES
        Route::resource('uploadfile/template/product/file', ProductFileController::class, ['only' => ['index', 'store']])
                ->names('template-product-file');
        //EXPORT FILES
        Route::get('/categories/list/file/xlsx', function () {
                return (new CategoriesFileController)->export()->download('categorias.xlsx');
        })->name('cotegory-export');

        Route::resource('uploadfile/file/company', importCategoriesController::class, ['only' => ['index', 'store']])
                ->names('file-company-txt');
});
