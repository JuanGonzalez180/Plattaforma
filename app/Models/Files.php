<?php

namespace App\Models;

use App\Blog;
use App\Category;
use App\Company;
use App\Products;
use App\Projects;
use App\TypeProject;
use App\TendersVersions;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    use HasFactory;

    /**
     * type: Tipo de Archivo
     */
    protected $fillable = [
        'name',
        'type',
        'type_id',
        'extension',
        'date',
        'date_update'
    ];
    
    public function blog(){
        return $this->belongsToMany(Blog::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    public function company(){
        return $this->belongsToMany(Company::class);
    }

    public function products(){
        return $this->belongsToMany(Products::class);
    }

    public function projects(){
        return $this->belongsToMany(Projects::class);
    }

    public function tendersVersions(){
        return $this->belongsToMany(TendersVersions::class);
    }

    public function typeProjects(){
        return $this->belongsToMany(TypeProject::class);
    }
}
