<?php

namespace App\Models;

use App\Addresses;
use App\Company;
use App\Files;
use App\Interests;
use App\MetaData;
use App\User;
use App\SocialNetworks;
use App\TypeProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    use HasFactory;

    const PRODUCT_ERASER = 'Borrador';
    const PRODUCT_PUBLISH = 'Publicado';

    protected $fillable = [
        'name',
        'company_id',
        'user_id',
        'description',
        'image',
        'images',
        'date_start',
        'date_start',
        'status',
        'date',
        'date_update'
    ];

    public function isPublish(){
        return $this->status == Products::PRODUCT_PUBLISH;
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

    public function socialNetworks(){
        return $this->belongsToMany(SocialNetworks::class);
    }
}
