<?php

namespace App\Models;

use App\Models\Blog;
use App\Models\Team;
use App\Models\User;
use App\Models\Tags;
use App\Models\Files;
use App\Models\Image;
use App\Models\Brands;
use App\Models\Country;
use App\Models\Remarks;
use App\Models\Tenders;
use App\Models\Quotes;
use App\Models\MetaData;
use App\Models\Products;
use App\Models\Projects;
use App\Models\Catalogs;
use App\Models\Portfolio;
use App\Models\Addresses;
use App\Models\Interests;
use App\Models\TypesEntity;
use App\Models\Advertisings;
use App\Models\SocialNetworks;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use App\Models\CategoryService;
use Illuminate\Support\Facades\DB;
use App\Models\SocialNetworksRelation;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\CompanyTransformer;
use App\Models\AdvertisingPlansPaidImages;
use App\Transformers\CompanyDetailTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    public $transformer         = CompanyTransformer::class;
    public $transformerDetail   = CompanyDetailTransformer::class;

    const COMPANY_CREATED   = 'Creado';
    const COMPANY_APPROVED  = 'Aprobado';
    const COMPANY_REJECTED  = 'Rechazado';
    const COMPANY_BANNED    = 'Bloqueado';

    protected $casts = [
        'phone' => 'array'
    ];

    protected $fillable = [
        'name',
        'description',
        'type_entity_id',
        'nit',
        'country_code',
        'web',
        'phone',
        'status',
        'user_id',
        'slug'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'status',
        'user_id',
    ];

    public function type_entity()
    {
        return $this->belongsTo(TypesEntity::class);
    }

    public function type_company()
    {
        return $this->type_entity->type->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    public function portfolios()
    {
        return $this->hasMany(Portfolio::class);
    }

    public function catalogs()
    {
        return $this->hasMany(Catalogs::class);
    }

    public function projects()
    {
        return $this->hasMany(Projects::class);
    }

    public function tenders()
    {
        return $this->hasMany(Tenders::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quotes::class);
    }

    public function products()
    {
        return $this->hasMany(Products::class);
    }

    public function metaDatos()
    {
        return $this->hasMany(MetaData::class);
    }

    public function cover_Page(){
        return Image::where('imageable_id', $this->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
    }

    // public function socialNetworks(){
    //     return $this->belongsToMany(SocialNetworks::class);
    // }

    //Relacion Muchos a Muchos
    public function countries()
    {
        return $this->belongsToMany(Country::class);
    }

    //Relacion uno a mucho
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function teamsCount()
    {
        return Company::find($this->id)->teams->count();
    }

    // Relacion uno a uno polimorfica
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    // Relacion uno a uno polimorfica
    public function address()
    {
        return $this->morphOne(Addresses::class, 'addressable');
    }

    // Relacion uno a muchos polimorfica
    public function socialnetworks()
    {
        return $this->morphMany(SocialNetworksRelation::class, 'socialable');
    }

    public function companyCategoryServices()
    {
        return $this->belongsToMany(CategoryService::class);
    }

    // Relacion uno a muchos polimorfica
    public function files()
    {
        return $this->morphMany(Files::class, 'filesable');
    }

    // Relacion uno a muchos polimorfica
    public function advertisings()
    {
        return $this->morphMany(Advertisings::class, 'advertisingable');
    }

    // Relacion uno a muchos polimorfica
    public function tags()
    {
        return $this->morphMany(Tags::class, 'tagsable');
    }

    public function calification()
    {
        $remarks = Remarks::select('remarks.*')
            ->where('remarks.company_id', $this->id)
            ->avg('calification');

        return ($remarks) ? $remarks : 0;
    }

    //blogs
    public function fileSizeBlogs()
    {
        $files = Files::where('files.filesable_type', Blog::class)
            ->whereNotNull('files.size')
            ->join('blogs', 'blogs.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'blogs.company_id')
            ->where('companies.id', $this->id)
            ->sum('files.size');

        $images = DB::table('images')->where('images.imageable_type', Blog::class)
            ->whereNotNull('images.size')
            ->join('blogs', 'blogs.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'blogs.company_id')
            ->where('companies.id', $this->id)
            ->sum('images.size');


        return $files + $images;
    }

    public function fileCountBlogs()
    {
        $files = Files::where('files.filesable_type', Blog::class)
            ->whereNotNull('files.size')
            ->join('blogs', 'blogs.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'blogs.company_id')
            ->where('companies.id', $this->id)
            ->count();

        $images = DB::table('images')->where('images.imageable_type', Blog::class)
            ->whereNotNull('images.size')
            ->join('blogs', 'blogs.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'blogs.company_id')
            ->where('companies.id', $this->id)
            ->count();


        return $files + $images;
    }

    public function fileListBlogs()
    {
        $files = Files::select('files.url', 'files.size', 'files.updated_at')->where('files.filesable_type', Blog::class)
            ->whereNotNull('files.size')
            ->join('blogs', 'blogs.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'blogs.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $images = DB::table('images')->select('images.url', 'images.size', 'images.updated_at')
            ->where('images.imageable_type', Blog::class)
            ->whereNotNull('images.size')
            ->join('blogs', 'blogs.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'blogs.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $files = $images->merge($files);

        return $files->sortBy([['updated_at', 'desc']]);
    }

    // Marcas
    public function fileSizeBrands()
    {
        return DB::table('images')->where('images.imageable_type', Brands::class)
            ->whereNotNull('images.size')
            ->join('brands', 'brands.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'brands.company_id')
            ->where('companies.id', $this->id)
            ->sum('images.size');
    }

    public function fileCountBrands()
    {
        return DB::table('images')->where('images.imageable_type', Brands::class)
            ->whereNotNull('images.size')
            ->join('brands', 'brands.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'brands.company_id')
            ->where('companies.id', $this->id)
            ->count();
    }

    public function fileCountCatalogs()
    {
        $files = Files::where('files.filesable_type', Catalogs::class)
            ->whereNotNull('files.size')
            ->join('catalogs', 'catalogs.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'catalogs.company_id')
            ->where('companies.id', $this->id)
            ->count();

        $images = DB::table('images')->where('images.imageable_type', Catalogs::class)
            ->whereNotNull('images.size')
            ->join('catalogs', 'catalogs.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'catalogs.company_id')
            ->where('companies.id', $this->id)
            ->count();

        return $files + $images;
    }

    public function fileListAdvertising()
    {
        return DB::table('images')->select('images.url', 'images.size', 'images.updated_at')
            ->where('images.imageable_type', AdvertisingPlansPaidImages::class)
            ->whereNotNull('images.size')
            ->join('advertising_plans_paid_images', 'advertising_plans_paid_images.id', '=', 'images.imageable_id')
            ->join('advertisings', 'advertisings.id', '=', 'advertising_plans_paid_images.advertisings_id')
            ->join('registration_payments', 'registration_payments.paymentsable_id', '=', 'advertisings.id')
            ->where('registration_payments.company_id', $this->id)
            ->orderBy('images.created_at','desc')
            ->get();
    }

    public function fileCountAdvertising()
    {
        return DB::table('images')->select('images.url', 'images.size', 'images.updated_at')
            ->where('images.imageable_type', AdvertisingPlansPaidImages::class)
            ->whereNotNull('images.size')
            ->join('advertising_plans_paid_images', 'advertising_plans_paid_images.id', '=', 'images.imageable_id')
            ->join('advertisings', 'advertisings.id', '=', 'advertising_plans_paid_images.advertisings_id')
            ->join('registration_payments', 'registration_payments.paymentsable_id', '=', 'advertisings.id')
            ->where('registration_payments.company_id', $this->id)
            ->count();
    }

    public function fileSizeAdvertising()
    {
        return DB::table('images')->select('images.url', 'images.size', 'images.updated_at')
            ->where('images.imageable_type', AdvertisingPlansPaidImages::class)
            ->whereNotNull('images.size')
            ->join('advertising_plans_paid_images', 'advertising_plans_paid_images.id', '=', 'images.imageable_id')
            ->join('advertisings', 'advertisings.id', '=', 'advertising_plans_paid_images.advertisings_id')
            ->join('registration_payments', 'registration_payments.paymentsable_id', '=', 'advertisings.id')
            ->where('registration_payments.company_id', $this->id)
            ->sum('images.size');
    }

    public function adminCompany()
    {
        return $this->user_id;
    }

    public function adminUser()
    {
        return $this->user;
    }

    public function fileSizeCatalogs()
    {
        $files = Files::where('files.filesable_type', Catalogs::class)
            // ->whereNotNull('files.size')
            ->join('catalogs', 'catalogs.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'catalogs.company_id')
            ->where('companies.id', $this->id)
            ->sum('files.size');

        $images = DB::table('images')->where('images.imageable_type', Catalogs::class)
            ->whereNotNull('images.size')
            ->join('catalogs', 'catalogs.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'catalogs.company_id')
            ->where('companies.id', $this->id)
            ->sum('images.size');

        return $files + $images;
    }

    public function fileListBrands()
    {
        $files =  DB::table('images')->select('images.url', 'images.size', 'images.updated_at')
            ->where('images.imageable_type', Brands::class)
            ->whereNotNull('images.size')
            ->join('brands', 'brands.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'brands.company_id')
            ->where('companies.id', $this->id)
            ->get();

        return $files->sortBy([['updated_at', 'desc']]);
    }

    //Portafolio
    public function fileSizePortfolio()
    {
        $files = Files::where('files.filesable_type', Portfolio::class)
            // ->whereNotNull('files.size')
            ->join('portfolios', 'portfolios.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'portfolios.company_id')
            ->where('companies.id', $this->id)
            ->sum('files.size');

        $images = DB::table('images')->where('images.imageable_type', Portfolio::class)
            ->whereNotNull('images.size')
            ->join('portfolios', 'portfolios.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'portfolios.company_id')
            ->where('companies.id', $this->id)
            ->sum('images.size');

        return $files + $images;
    }

    public function fileCountPortfolio()
    {
        $files = Files::where('files.filesable_type', Portfolio::class)
            ->whereNotNull('files.size')
            ->join('portfolios', 'portfolios.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'portfolios.company_id')
            ->where('companies.id', $this->id)
            ->count();

        $images = DB::table('images')->where('images.imageable_type', Portfolio::class)
            ->whereNotNull('images.size')
            ->join('portfolios', 'portfolios.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'portfolios.company_id')
            ->where('companies.id', $this->id)
            ->count();

        return $files + $images;
    }

    public function fileListPortfolio()
    {
        $files = Files::select('files.url', 'files.size', 'files.updated_at')
            ->where('files.filesable_type', Portfolio::class)
            ->whereNotNull('files.size')
            ->join('portfolios', 'portfolios.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'portfolios.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $images = DB::table('images')
            ->select('images.url', 'images.size', 'images.updated_at')
            ->where('images.imageable_type', Portfolio::class)
            ->whereNotNull('images.size')
            ->join('portfolios', 'portfolios.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'portfolios.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $files = $images->merge($files);

        return $files->sortBy([['updated_at', 'desc']]);
    }

    public function fileListCatalog()
    {
        $files = Files::select('files.url', 'files.size', 'files.updated_at')
            ->where('files.filesable_type', Catalogs::class)
            ->whereNotNull('files.size')
            ->join('catalogs', 'catalogs.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'catalogs.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $images = DB::table('images')
            ->select('images.url', 'images.size', 'images.updated_at')
            ->where('images.imageable_type', Catalogs::class)
            ->whereNotNull('images.size')
            ->join('catalogs', 'catalogs.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'catalogs.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $files = $images->merge($files);

        return $files->sortBy([['updated_at', 'desc']]);
    }

    //Proyectos
    public function fileSizeProject()
    {
        $files = Files::where('files.filesable_type', projects::class)
            ->whereNotNull('files.size')
            ->join('projects', 'projects.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->where('companies.id', $this->id)
            ->sum('files.size');

        $images = DB::table('images')
            ->where('images.imageable_type', projects::class)
            ->whereNotNull('images.size')
            ->join('projects', 'projects.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->where('companies.id', $this->id)
            ->sum('images.size');

        return $files + $images;
    }

    public function fileCountProject()
    {
        $files = Files::where('files.filesable_type', projects::class)
            ->whereNotNull('files.size')
            ->join('projects', 'projects.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->where('companies.id', $this->id)
            ->count();

        $images = DB::table('images')->where('images.imageable_type', projects::class)
            ->whereNotNull('images.size')
            ->join('projects', 'projects.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->where('companies.id', $this->id)
            ->count();

        return $files + $images;
    }

    public function fileListProject()
    {
        $files = Files::select('files.url', 'files.size', 'files.updated_at')
            ->where('files.filesable_type', projects::class)
            ->whereNotNull('files.size')
            ->join('projects', 'projects.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $images = DB::table('images')
            ->select('images.url', 'images.size', 'images.updated_at')
            ->where('images.imageable_type', projects::class)
            ->whereNotNull('images.size')
            ->join('projects', 'projects.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $files = $images->merge($files);

        return $files->sortBy([['updated_at', 'desc']]);
    }

    //Productos
    public function fileSizeProduct()
    {
        $files = Files::where('files.filesable_type', Products::class)
            ->whereNotNull('files.size')
            ->join('products', 'products.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.id', $this->id)
            ->sum('files.size');

        $images = DB::table('images')->where('images.imageable_type', Products::class)
            ->whereNotNull('images.size')
            ->join('products', 'products.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.id', $this->id)
            ->sum('images.size');

        return $files + $images;
    }

    public function fileCountProduct()
    {
        $files = Files::where('files.filesable_type', Products::class)
            ->whereNotNull('files.size')
            ->join('products', 'products.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.id', $this->id)
            ->count();

        $images = DB::table('images')->where('images.imageable_type', Products::class)
            ->whereNotNull('images.size')
            ->join('products', 'products.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.id', $this->id)
            ->count();

        return $files + $images;
    }

    public function fileListProduct()
    {
        $files = Files::select('files.url', 'files.size', 'files.updated_at')
            ->where('files.filesable_type', Products::class)
            ->whereNotNull('files.size')
            ->join('products', 'products.id', '=', 'files.filesable_id')
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $images = DB::table('images')
            ->select('images.url', 'images.size', 'images.updated_at')
            ->where('images.imageable_type', Products::class)
            ->whereNotNull('images.size')
            ->join('products', 'products.id', '=', 'images.imageable_id')
            ->join('companies', 'companies.id', '=', 'products.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $files = $images->merge($files);

        return $files->sortBy([['updated_at', 'desc']]);
    }

    //Licitaciones
    public function fileSizeTender()
    {
        return $this->fileSizeTenderVersion() + $this->fileSizeTenderCompany();
    }

    public function fileCountTender()
    {
        return $this->fileCountTenderVersion() + $this->fileCountTenderCompany();
    }

    public function fileListTender()
    {
        $filesTenderVersion = Files::select('files.url', 'files.size', 'files.updated_at')
            ->where('files.filesable_type', TendersVersions::class)
            ->whereNotNull('files.size')
            ->join('tenders_versions', 'tenders_versions.id', '=', 'files.filesable_id')
            ->join('tenders', 'tenders.id', '=', 'tenders_versions.tenders_id')
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->where('companies.id', $this->id)
            ->get();

        $filesTenderCompanies = Files::select('files.url', 'files.size', 'files.updated_at')
            ->where('files.filesable_type', TendersCompanies::class)
            ->whereNotNull('files.size')
            ->join('tenders_companies', 'tenders_companies.id', '=', 'files.filesable_id')
            ->where('tenders_companies.company_id', $this->id)
            ->get();

        $files = $filesTenderVersion->merge($filesTenderCompanies);

        return $filesTenderVersion->sortBy([['updated_at', 'desc']]);
    }

    public function fileSizeTenderVersion()
    {
        return Files::where('files.filesable_type', TendersVersions::class)
            ->whereNotNull('files.size')
            ->join('tenders_versions', 'tenders_versions.id', '=', 'files.filesable_id')
            ->join('tenders', 'tenders.id', '=', 'tenders_versions.tenders_id')
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->where('companies.id', $this->id)
            ->sum('files.size');
    }

    public function fileCountTenderVersion()
    {
        return Files::where('files.filesable_type', TendersVersions::class)
            ->whereNotNull('files.size')
            ->join('tenders_versions', 'tenders_versions.id', '=', 'files.filesable_id')
            ->join('tenders', 'tenders.id', '=', 'tenders_versions.tenders_id')
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->where('companies.id', $this->id)
            ->count();
    }

    public function fileSizeTenderCompany()
    {
        return Files::where('files.filesable_type', TendersCompanies::class)
            ->whereNotNull('files.size')
            ->join('tenders_companies', 'tenders_companies.id', '=', 'files.filesable_id')
            ->where('tenders_companies.company_id', $this->id)
            ->sum('files.size');
    }

    public function fileCountTenderCompany()
    {
        return Files::where('files.filesable_type', TendersCompanies::class)
            ->whereNotNull('files.size')
            ->join('tenders_companies', 'tenders_companies.id', '=', 'files.filesable_id')
            ->where('tenders_companies.company_id', $this->id)
            ->count();
    }

    public function fileListTotal()
    {
        $files = $this->fileListBrands()
            ->merge($this->fileListBlogs())
            ->merge($this->fileListProject())
            ->merge($this->fileListProduct())
            ->merge($this->fileListPortfolio())
            ->merge($this->fileListTender())
            ->merge($this->fileListAdvertising());

        return $files->sortBy([['updated_at', 'desc']]);
    }

    public function fileSizeTotal()
    {
        $brands          = $this->fileSizeBrands();
        $blog            = $this->fileSizeBlogs();
        $project         = $this->fileSizeProject();
        $product         = $this->fileSizeProduct();
        $portfolio       = $this->fileSizePortfolio();
        $catalog         = $this->fileSizeCatalogs();
        $tender          = $this->fileSizeTender();
        $advertising     = $this->fileSizeAdvertising();

        return $brands + $blog + $project + $product + $portfolio + $catalog + $tender + $advertising;
    }

    public function fileCountTotal()
    {
        $brands          = $this->fileCountBrands();
        $blog            = $this->fileCountBlogs();
        $project         = $this->fileCountProject();
        $product         = $this->fileCountProduct();
        $portfolio       = $this->fileCountPortfolio();
        $tender          = $this->fileCountTender();
        $catalog         = $this->fileCountCatalogs();
        $advertising     = $this->fileCountAdvertising();

        return $brands + $blog + $project + $product + $portfolio + $tender + $catalog + $advertising;
    }

    public function fileSizeTotalDetail()
    {
        $item['Blogs']          = [$this->fileSizeBlogs(), $this->fileSizeBlogs()];
        $item['Marcas']         = [$this->fileCountBrands(), $this->fileSizeBrands()];
        $item['Licitaciones']   = [$this->fileCountTender(), $this->fileSizeTender()];
        $item['Productos']      = [$this->fileCountProduct(), $this->fileSizeProduct()];
        $item['Portafolios']    = [$this->fileCountPortfolio(), $this->fileSizePortfolio()];
        $item['Projectos']      = [$this->fileCountProject(), $this->fileSizeProject()];
        $item['Catalogos']      = [$this->fileCountCatalogs(), $this->fileSizeCatalogs()];
        $item['Publicidades']   = [$this->fileCountAdvertising(), $this->fileSizeAdvertising()];

        return $item;
    }

    public function companyStatusPayment()
    {
        return (($this->type_company() == 'Oferta') && ($this->status == Company::COMPANY_APPROVED));
    }

    public function total()
    {
        $total = [];

        $companySinTransform = Company::findOrFail($this->id);

        $total['team'] = Team::where('company_id', $companySinTransform->id)
            ->where('status', Team::TEAM_APPROVED)
            ->get()
            ->count();

        $total['projects'] = $companySinTransform->projects
            ->where('visible', Projects::PROJECTS_VISIBLE)
            ->count();

        $total['tenders'] = $companySinTransform->tenders
            ->count();

        $total['products'] = $companySinTransform->products
            ->where('status', Products::PRODUCT_PUBLISH)
            ->count();

        $total['blogs'] = $companySinTransform->blogs
            ->where('status', Blog::BLOG_PUBLISH)
            ->count();

        $total['portfolio'] = $companySinTransform->portfolios
            ->where('status', Portfolio::PORTFOLIO_PUBLISH)
            ->count();

        $total['catalog'] = $companySinTransform->catalogs
            ->where('status', Catalogs::CATALOG_PUBLISH)
            ->count();

        $total['remarks'] = Remarks::select('remarks.*')
            ->where('remarks.company_id', $companySinTransform->id)
            ->count();

        return $total;
    }

    // Relacion uno a muchos polimorfica
    public function remarks()
    {
        return $this->morphMany(Remarks::class, 'remarksable');
    }

    // Relacion uno a muchos polimorfica
    public function interests()
    {
        return $this->morphMany(Interests::class, 'interestsable');
    }

    // relacion de uno a muchos 
    public function brands()
    {
        return $this->hasMany(Brands::class);
    }

    public function formatSize($file_size)
    {
        if (round(($file_size / pow(1024, 2)), 3) < '1') {
            $file = round(($file_size * 0.01), 1) . ' KB';
        } else if (round(($file_size / pow(1024, 2)), 1) < '1024') {
            $file = round(($file_size / pow(1024, 2)), 1) . ' MB';
        } else if (round(($file_size / pow(1024, 2)), 1) >= '1024') {
            $file = round(($file_size / pow(1024, 2)), 1) . ' GB';
        }

        return $file;
    }

    public function userIds()
    {
        $teams = Team::select('user_id')
            ->where('company_id', $this->id)
            ->where('status','Aprobado')
            ->pluck('user_id')
            ->all();

        $users = array_merge([$this->user_id], $teams);
        
        return $users;
    }

    public function emails()
    {
        $teams = Team::select('users.email')
        ->where('company_id', $this->id)
        ->where('status','Aprobado')
        ->join('users','users.id','=','teams.user_id')
        ->pluck('users.email')
        ->all();

        $email = array_merge([$this->user->email], $teams);
    
        return $email;
    }
}
