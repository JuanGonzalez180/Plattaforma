<?php

namespace App\Models;

use App\Models\Tags;
use App\Models\User;
use App\Models\Company;
use App\Models\Remarks;
use App\Models\Projects;
use App\Models\Interests;
use App\Models\QueryWall;
use App\Models\Notifications;
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

    public function tendersVersionLastPublishTags(){
        $tenderVersionLastPublish = $this->tendersVersionLastPublish();
        if( $tenderVersionLastPublish ){
            $tags = Tags::where('tagsable_id', $tenderVersionLastPublish->id)
                ->where('tagsable_type', TendersVersions::class)
                ->orderBy('name','asc')
                ->pluck('name');
    
            return $tags;
        }
        return [];
    }

    public function tendersVersionLastPublish(){
        $tenderPublish = $this->tendersVersion
            ->where( 'status','<>', TendersVersions::LICITACION_CREATED )
            ->sortBy([ ['created_at', 'desc'] ]);
        
        if( $tenderPublish && $tenderPublish->count() ){
            return $tenderPublish->first();
        }

        return [];
    }

    // public function tendersVersionLastPublish(){
    //     $tenderPublish = $this->tendersVersion
    //         ->whereIn( 'status', TendersVersions::LICITACION_PUBLISH)
    //         ->sortBy([ ['created_at', 'desc'] ]);
        
    //     if( $tenderPublish && $tenderPublish->count() ){
    //         return $tenderPublish->first();
    //     }

    //     return [];
    // }
    
    public function tenderCompanies(){
        return $this->hasMany(TendersCompanies::class, 'tender_id');
    }

    public function isWinner(){
        $tenderVersion = TendersCompanies::where('tender_id', $this->id)
            ->where('winner', TendersCompanies::WINNER_TRUE)
            ->exists();

        return $tenderVersion;
    }

    // Relacion uno a muchos polimorfica
    public function notifications(){
        return $this->morphMany(Notifications::class, 'notificationsable');
    }
    
    // Relacion uno a muchos polimorfica
    public function interests(){
        return $this->morphMany(Interests::class, 'interestsable');
    }
}
