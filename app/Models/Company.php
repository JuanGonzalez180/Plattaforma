<?php

namespace App\Models;

use App\Blog;
use App\Countries;
use App\Products;
use App\Projects;
use App\TypesEntity;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

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

    public function projects(){
        return $this->hasMany(Projects::class);
    }

    public function products(){
        return $this->hasMany(Products::class);
    }
}
