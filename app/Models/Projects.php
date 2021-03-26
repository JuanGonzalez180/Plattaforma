<?php

namespace App\Models;

use App\Models\Addresses;
use App\Models\Company;
use App\Models\Files;
use App\Models\Interests;
use App\Models\MetaData;
use App\Models\User;
use App\Models\TypeProject;
use App\Models\Image;
use App\Models\SocialNetworksRelation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    use HasFactory;

    const PROJECTS_ERASER = 'Borrador';
    const PROJECTS_PUBLISH = 'Publicado';

    protected $fillable = [
        'name',
        'company_id',
        'user_id',
        'type_projects_id',
        'description',
        'date_start',
        'date_end',
        'status'
    ];

    public function isPublish(){
        return $this->status == Projects::PROJECTS_PUBLISH;
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function types_projects(){
        return $this->belongsTo(TypeProject::class);
    }

    public function files(){
        return $this->belongsToMany(Files::class);
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
}
