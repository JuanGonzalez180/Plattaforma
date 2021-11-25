<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Files;
use App\Models\Image;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Remarks;
use App\Models\MetaData;
use App\Models\Addresses;
use App\Models\Interests;
use App\Models\TypeProject;
use App\Models\Advertisings;
use App\Models\Notifications;
use App\Models\TendersVersions;
use Illuminate\Support\Facades\DB;
use App\Models\SocialNetworksRelation;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\ProjectsTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Projects extends Model
{
    use HasFactory;

    const PROJECTS_ERASER   = 'Borrador';
    const PROJECTS_PUBLISH  = 'Publicado';

    const PROJECTS_VISIBLE      = 'Visible';
    const PROJECTS_VISIBLE_NO   = 'No-Visible';

    public $transformer = ProjectsTransformer::class;

    protected $fillable = [
        'name',
        'company_id',
        'user_id',
        'type_project_id',
        'description',
        'date_start',
        'date_end',
        'meters',
        'status',
        'visible'
    ];

    public function isPublish(){
        return $this->status == Projects::PROJECTS_PUBLISH;
    }
    
    public function isVisible(){
        return $this->visible == Projects::PROJECTS_VISIBLE;
    }

    public function tenders(){
        return $this->hasMany(Tenders::class,'project_id','id');
    }

    public function tendersEvents()
    {
        $tenders = $this->tenders;

        $notification = [];

        foreach ($tenders as $tender) {
            $versionLast = $tender->tendersVersionLast();
            if($versionLast->status == TendersVersions::LICITACION_PUBLISH){
                $notification[] = [
                    "type" => 'initial',
                    "date" => $tender->created_at->format('Y-m-d'),
                    "name" => $tender->name,
                ];
                $notification[] = [
                    "type" => 'final',
                    "date" => $versionLast->date,
                    "name" => $tender->name,
                ];
            }
        }

        $notification = collect($notification)->sortBy('date');

        return array_values($notification->toArray());

    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
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

    // Relacion uno a muchos polimorfica
    public function metadata(){
        return $this->morphMany(MetaData::class, 'metadatable');
    }
    
    public function projectTypeProject(){
        return $this->belongsToMany(TypeProject::class);
    }

    // Relacion uno a muchos polimorfica
    public function advertisings(){
        return $this->morphMany(Advertisings::class, 'advertisingable');
    }

    // Relacion uno a muchos polimorfica
    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }

    // Relacion uno a muchos polimorfica
    public function notifications(){
        return $this->morphMany(Notifications::class, 'notificationsable');
    }

    // Relacion uno a muchos polimorfica
    public function remarks(){
        return $this->morphMany(Remarks::class, 'remarksable');
    }

    // Relacion uno a muchos polimorfica
    public function interests(){
        return $this->morphMany(Interests::class, 'interestsable');
    }
}
