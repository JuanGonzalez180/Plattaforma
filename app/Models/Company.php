<?php

namespace App\Models;

use App\Models\Blog;
use App\Models\Team;
use App\Models\User;
use App\Models\Files;
use App\Models\Image;
use App\Models\Country;
use App\Models\Tenders;
use App\Models\MetaData;
use App\Models\Products;
use App\Models\Projects;
use App\Models\Portfolio;
use App\Models\Addresses;
use App\Models\Interests;
use App\Models\TypesEntity;
use App\Models\SocialNetworks;
use App\Models\SocialNetworksRelation;
use App\Models\CategoryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\CompanyTransformer;
use App\Transformers\CompanyDetailTransformer;

class Company extends Model
{
    use HasFactory;

    public $transformer = CompanyTransformer::class;
    public $transformerDetail = CompanyDetailTransformer::class;

    const COMPANY_CREATED   = 'Creado';
    const COMPANY_APPROVED  = 'Aprobado';
    const COMPANY_REJECTED  = 'Rechazado';

    protected $fillable = [
        'name',
        'description',
        'type_entity_id',
        'nit',
        'country_code',
        'web',
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

    public function type_entity(){
        return $this->belongsTo(TypesEntity::class);
    }

    public function type_company(){
        return $this->type_entity->type->name;
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function blogs(){
        return $this->hasMany(Blog::class);
    }

    public function portfolios(){
        return $this->hasMany(Portfolio::class);
    }

    public function projects(){
        return $this->hasMany(Projects::class);
    }

    public function tenders(){
        return $this->hasMany(Tenders::class);
    }

    public function products(){
        return $this->hasMany(Products::class);
    }

    public function interests(){
        return $this->belongsToMany(Interests::class);
    }

    public function metaDatos(){
        return $this->hasMany(MetaData::class);
    }

    /*public function socialNetworks(){
        return $this->belongsToMany(SocialNetworks::class);
    }*/

    //Relacion Muchos a Muchos
    public function countries(){
        return $this->belongsToMany(Country::class);
    }

    //Relacion uno a mucho
    public function teams(){
        return $this->hasMany(Team::class);
    }

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }

    // Relacion uno a uno polimorfica
    public function address(){
        return $this->morphOne(Addresses::class, 'addressable');
    }

    // Relacion uno a muchos polimorfica
    public function socialnetworks(){
        return $this->morphMany(SocialNetworksRelation::class, 'socialable');
    }

    public function companyCategoryServices(){
        return $this->belongsToMany(CategoryService::class);
    }

    // Relacion uno a muchos polimorfica
    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }
    
    /*public function reviewsA(){
        $company->
    }*/

    public function calification(){
        $remarks = Remarks::select('remarks.*')
                ->where('remarks.company_id', $this->id )
                ->avg('calification');
        if( $remarks ){
            return $remarks;
        }
        return 0;
    }

    public function total(){
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

        // $total['portfolio'] = count($companySinTransform->files);
        $total['portfolio'] = $companySinTransform->portfolios
                                ->where('status', Portfolio::PORTFOLIO_PUBLISH)
                                ->count();
        
        $total['remarks'] = Remarks::select('remarks.*')
                                ->where('remarks.company_id', $companySinTransform->id )
                                ->count();

        return $total;
    }
}