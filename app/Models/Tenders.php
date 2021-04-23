<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Remarks;
use App\Models\Projects;
use App\Models\Interests;
use App\Models\TendersVersions;
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

    public function queryWalls(){
        return $this->hasMany(QueryWall::class);
    }

    public function remarks(){
        return $this->hasMany(Remarks::class);
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
}
