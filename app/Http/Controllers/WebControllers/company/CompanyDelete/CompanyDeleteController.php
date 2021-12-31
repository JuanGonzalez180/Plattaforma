<?php

namespace App\Http\Controllers\WebControllers\company\CompanyDelete;

use App\Models\User;
use App\Models\Tags;
use App\Models\Blog;
use App\Models\Team;
use App\Models\Image;
use App\Models\Files;
use App\Models\Brands;
use App\Models\Remarks;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Projects;
use App\Models\Catalogs;
use App\Models\Products;
use App\Models\QueryWall;
use App\Models\Interests;
use App\Models\Portfolio;
use App\Models\Addresses;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\CategoryTenders;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CompanyDeleteController extends Controller
{
    public $routeFile = 'public/';

    public function __invoke($id)
    {
        $company = Company::find($id);

        // $email_message = $this->emailMessageInfo($company);
        
        //blogs
        $this->deleteCompanyBlogs($company->blogs->pluck('id'));
        // //portafolios
        $this->deleteCompanyPortfolio($company->portfolios->pluck('id'));
        //catalogos
        $this->deleteCompanyCatalog($company->catalogs->pluck('id'));
        //Productos
        $this->deleteCompanyProducts($company->products->pluck('id'));
        //Marcas
        $this->updateCompanyBrands($company->brands->pluck('id'));
        //Licitaciones
        $this->deleteCompanyTenders($company->tenders->pluck('id'));
        //Reseñas
        $this->deleteRemarks([$company->id], Company::class);
        //
        $this->deleteInterests([$company->id], Company::class);
        //Etiquetas
        $this->deleteTags([$company->id], Company::class);
        // Projectos
        $this->deleteCompanyProjects($company->projects->pluck('id'));
        //imagen de perfil
        $this->deleteImage([$company->id], Company::class);
        //imagen de portada
        $this->deleteImage([$company->id], 'App\Models\Company\CoverPage');
        //address
        $this->deleteAddresses([$company->id], Company::class);
        //categirias de l servicio de la compañia
        $this->deleteCompanyCategoryService($company->id);
        //Muro de consultas
        $this->deleteCompanyIdQueryWall($company->id);
        //categirias de l servicio de la compañia
        $this->deleteCompanyTeamUsers($company->id);
        //Compañia
        $this->deleteCompany($company->id);
    }

    public function deleteCompany($company_id)
    {
        $company = Company::find($company_id);

        $user = $company->user;
        $company->delete();

        $this->deleteImage([$user->id], User::class);
        $user->delete();

    }

    public function deleteCompanyTeamUsers($company_id)
    {
        $users = $this->deleteCompanyTeams($company_id);
        $this->deleteCompanyUsers($users);
    }

    public function deleteCompanyUsers($users_id)
    {
        $this->deleteImage([$users_id], User::class);
        $this->updateCompanyBrandsUser($users_id);

        User::where('id', $users_id)
            ->delete();
    }

    public function deleteCompanyTeams($company_id)
    {
        $users = Team::where('company_id', $company_id)
            ->pluck('user_id');

        Team::where('company_id', $company_id)
            ->delete();

        return $users;
    }

    public function deleteCompanyTenders($tender_ids)
    {
        $this->deleteCompanyQueryWall($tender_ids, Tenders::class);
        $this->deleteNotifications($tender_ids, Tenders::class);
        $this->deleteInterests($tender_ids, Tenders::class);
        $this->deleteRemarks($tender_ids, Tenders::class);
        $this->deleteCompanyTenderCategories($tender_ids);
        $this->deleteCompanyTenderCompanies($tender_ids);
        $this->deleteCompanyTenderVersion($tender_ids);

        Tenders::whereIn('id', $tender_ids)
            ->delete();
    }

    public function deleteCompanyQueryWall($array_id, $classModel)
    {
        QueryWall::whereIn('querysable_id', $array_id)
            ->where('querysable_type', $classModel)
            ->delete();
    }

    public function deleteCompanyIdQueryWall($company_id)
    {
        QueryWall::where('company_id', $company_id)
            ->delete();
    }

    public function deleteCompanyBlogs($blog_ids)
    {
        $this->deleteFiles($blog_ids, Blog::class);
        $this->deleteImage($blog_ids, Blog::class);

        Blog::whereIn('id', $blog_ids)
            ->delete();
    }

    public function deleteCompanyTenderCategories($tender_ids)
    {
        CategoryTenders::whereIn('tenders_id', $tender_ids)
            ->delete();
    }

    public function deleteCompanyTenderCompanies($tender_ids)
    {
        $tenderCompanies = TendersCompanies::whereIn('tender_id', $tender_ids)
            ->pluck('id');

        $this->deleteNotifications($tenderCompanies, TendersCompanies::class);
        $this->deleteRemarks($tenderCompanies, TendersCompanies::class);
        $this->deleteFiles($tenderCompanies, TendersCompanies::class);

        TendersCompanies::whereIn('id', $tenderCompanies)
            ->delete();
    }

    public function deleteCompanyTenderVersion($tender_ids)
    {
        $tenderVersions = TendersVersions::whereIn('tenders_id', $tender_ids)
            ->pluck('id');

        $this->deleteFiles($tenderVersions, TendersVersions::class);
        $this->deleteTags($tenderVersions, TendersVersions::class);
        $this->deleteNotifications($tenderVersions, TendersVersions::class);

        TendersVersions::whereIn('id', $tenderVersions)
            ->delete();
    }

    public function deleteCompanyPortfolio($portfolio_ids)
    {
        $this->deleteFiles($portfolio_ids, Portfolio::class);
        $this->deleteImage($portfolio_ids, Portfolio::class);
        $this->deleteTags($portfolio_ids, Portfolio::class);

        Portfolio::whereIn('id', $portfolio_ids)
            ->delete();
    }

    public function deleteCompanyCatalog($catalog_ids)
    {
        $this->deleteFiles($catalog_ids, Catalogs::class);
        $this->deleteImage($catalog_ids, Catalogs::class);
        $this->deleteTags($catalog_ids, Catalogs::class);

        Catalogs::whereIn('id', $catalog_ids)
            ->delete();
    }

    public function deleteCompanyProducts($product_ids)
    {
        $this->deleteFiles($product_ids, Products::class);
        $this->deleteImage($product_ids, Products::class);
        $this->deleteTags($product_ids, Products::class);
        $this->deleteNotifications($product_ids, Products::class);
        $this->deleteRemarks($product_ids, Products::class);
        $this->deleteInterests($product_ids, Products::class);
        $this->deleteCategoryProduct($product_ids);
        $this->deleteCategoryServiceProduct($product_ids);
        $this->deleteCompanyTempFilesProducts($product_ids);

        Products::whereIn('id', $product_ids)
            ->delete();
    }

    public function updateCompanyBrands($brand_id)
    {
        $brands = Brands::whereIn('id', $brand_id)->get();

        $brands->map(function ($item, $key) {
            $item->company_id = 0;
            return $item->save();
        });
    }

    public function updateCompanyBrandsUser($user_id)
    {
        $brands = Brands::whereIn('user_id', $user_id)->get();

        $brands->map(function ($item, $key) {
            $item->user_id = 0;
            return $item->save();
        });
    }

    public function deleteCategoryProduct($product_ids)
    {
        DB::table('category_products')
            ->whereIn('products_id', $product_ids)
            ->delete();
    }

    public function deleteCompanyCategoryService($company_id)
    {
        DB::table('category_service_company')
            ->where('company_id', $company_id)
            ->delete();
    }

    public function deleteCategoryServiceProduct($product_ids)
    {
        DB::table('category_service_products')
            ->whereIn('products_id', $product_ids)
            ->delete();
    }

    public function deleteCompanyProjects($projects_ids)
    {
        $this->deleteFiles($projects_ids, Projects::class);
        $this->deleteImage($projects_ids, Projects::class);
        $this->deleteRemarks($projects_ids, Projects::class);
        $this->deleteAddresses($projects_ids, Projects::class);
        $this->deleteInterests($projects_ids, Projects::class);
        $this->deleteCompanyProjectTypeProjects($projects_ids);
        $this->deleteNotifications($projects_ids, Projects::class);
        $this->deleteCompanyQueryWall($projects_ids, Projects::class);

        Projects::whereIn('id', $projects_ids)
            ->delete();
    }

    public function deleteCompanyProjectTypeProjects($projects_ids)
    {
        DB::table('projects_type_project')
            ->whereIn('projects_id', $projects_ids)
            ->delete();
    }

    public function deleteFiles($array_id, $classModel)
    {
        $files  = Files::whereIn('filesable_id', $array_id)
            ->where('filesable_type', $classModel)
            ->get();

        foreach ($files as $file) {
            Storage::disk('local')->delete($this->routeFile . $file->url);
            $file->delete();
        }
    }

    public function deleteImage($array_id, $classModel)
    {
        $images  = Image::whereIn('imageable_id', $array_id)
            ->where('imageable_type', $classModel)
            ->get();

        foreach ($images as $image) {
            Storage::disk('local')->delete($this->routeFile . $image->url);
            //elimina la imagen
            Image::where('imageable_id', $image->imageable_id)
                ->where('imageable_type', $image->imageable_type)
                ->delete();
        }
    }

    public function deleteTags($array_id, $classModel)
    {
        Tags::whereIn('tagsable_id', $array_id)
            ->where('tagsable_type', $classModel)
            ->delete();
    }

    public function deleteCompanyTempFilesProducts($product_ids)
    {
        if (Schema::hasTable('temp_product_files')) {
            DB::table('temp_product_files')
                ->whereIn('product_id', $product_ids)
                ->where('status', 'false')
                ->delete();
        }
    }

    public function deleteRemarks($array_id, $classModel)
    {
        Remarks::whereIn('remarksable_id', $array_id)
            ->where('remarksable_type', $classModel)
            ->delete();
    }

    public function deleteNotifications($array_id, $classModel)
    {
        Notifications::whereIn('notificationsable_id', $array_id)
            ->where('notificationsable_type', $classModel)
            ->delete();
    }

    public function deleteAddresses($array_id, $classModel)
    {
        Addresses::whereIn('addressable_id', $array_id)
            ->where('addressable_type', $classModel)
            ->delete();
    }

    public function deleteInterests($array_id, $classModel)
    {
        Interests::whereIn('interestsable_id', $array_id)
            ->where('interestsable_type', $classModel)
            ->delete();
    }

    public function emailMessageInfo($company)
    {
        return 0;
    }
}
