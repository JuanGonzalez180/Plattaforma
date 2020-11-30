<?php

namespace App\Models;

use App\Addresses;
use App\Blog;
use App\Countries;
use App\Files;
use App\Interests;
use App\MetaData;
use App\Products;
use App\Projects;
use App\SocialNetworks;
use App\TypesEntity;
use App\User;
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
        'country_id',
        'web',
        'image',
        'status',
        'user_id',
        'date',
        'date_update'
    ];

    public function entity(){
        return $this->belongsTo(TypesEntity::class);
    }

    public function country(){
        return $this->belongsTo(Countries::class);
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
}
