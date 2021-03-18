<?php

namespace App\Models;

use App\Addresses;
use App\Company;
use App\Files;
use App\Interests;
use App\MetaData;
use App\User;
use App\TypeProject;
use App\SocialNetworksRelation;
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
        'description',
        'image',
        'images',
        'date_start',
        'date_end',
        'status',
        'date',
        'date_update'
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

    public function addresses(){
        return $this->hasMany(Addresses::class);
    }

    public function files(){
        return $this->belongsToMany(Files::class);
    }

    public function interests(){
        return $this->belongsToMany(Interests::class);
    }

    public function metaDatos(){
        return $this->hasMany(MetaData::class);
    }

    public function typesProjects(){
        return $this->belongsToMany(TypeProject::class);
    }

    // Relacion uno a muchos polimorfica
    public function socialnetworks(){
        return $this->morphMany(SocialNetworksRelation::class, 'socialable');
    }
}
