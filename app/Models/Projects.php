<?php

namespace App\Models;

use App\Models\User;
use App\Models\Files;
use App\Models\Image;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Addresses;
use App\Models\Interests;
use App\Models\MetaData;
use App\Models\TypeProject;
use App\Models\SocialNetworksRelation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\ProjectsTransformer;

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
        return $this->hasMany(Tenders::class);
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
    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }
}
