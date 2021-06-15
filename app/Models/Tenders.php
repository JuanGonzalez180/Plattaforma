<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Remarks;
use App\Models\Projects;
use App\Models\Interests;
use App\Models\QueryWall;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\TendersTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenders extends Model
{
    use HasFactory;

    public $transformer = TendersTransformer::class;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'company_id',
        'user_id'
    ];

    public function project(){
        return $this->belongsTo(Projects::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }
    
    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function interests(){
        return $this->belongsToMany(Interests::class);
    }

    public function remarks(){
        return $this->hasMany(Remarks::class);
    }

    // Relacion uno a muchos polimorfica
    public function querywalls(){
        return $this->morphMany(QueryWall::class, 'querysable');
    }

    // Nuevo
    public function tenderCategories(){
        return $this->belongsToMany(Category::class);
    }

    public function tendersVersion(){
        return $this->hasMany(TendersVersions::class);
    }

    public function tendersVersionLast(){
        if( count($this->tendersVersion) && $this->tendersVersion[0] ){
            return $this->tendersVersion[count($this->tendersVersion)-1];
        }

        return [];
    }

    public function tendersVersionLastPublish(){
        $tenderPublish = $this->tendersVersion
            ->where( 'status', TendersVersions::LICITACION_PUBLISH )
            ->sortBy([ ['created_at', 'desc'] ]);
        
        if( $tenderPublish && $tenderPublish->count() ){
            return $tenderPublish->first();
        }

        return [];
    }
    
    public function tenderCompanies(){
        return $this->hasMany(TendersCompanies::class, 'tender_id');
    }
    
}
