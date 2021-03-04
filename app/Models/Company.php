<?php

namespace App\Models;

use App\Models\Addresses;
use App\Models\Blog;
use App\Models\Country;
use App\Models\Files;
use App\Models\Interests;
use App\Models\MetaData;
use App\Models\Products;
use App\Models\Projects;
use App\Models\SocialNetworks;
use App\Models\TypesEntity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    const COMPANY_CREATED = 'Creado';
    const COMPANY_APPROVED = 'Aprobado';
    const COMPANY_REJECTED = 'Rechazado';

    protected $fillable = [
        'name',
        'type_entity_id',
        'nit',
        'country_code',
        'web',
        'status',
        'user_id'
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

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function blogs(){
        return $this->hasMany(Blog::class);
    }

    public function files(){
        return $this->belongsToMany(Files::class);
    }

    public function projects(){
        return $this->hasMany(Projects::class);
    }

    public function products(){
        return $this->hasMany(Products::class);
    }

    public function addresses(){
        return $this->hasMany(Addresses::class);
    }

    public function interests(){
        return $this->belongsToMany(Interests::class);
    }

    public function metaDatos(){
        return $this->hasMany(MetaData::class);
    }

    public function socialNetworks(){
        return $this->belongsToMany(SocialNetworks::class);
    }

    //Relacion Muchos a Muchos
    public function countries(){
        return $this->belongsToMany(Country::class);
    }

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }
}