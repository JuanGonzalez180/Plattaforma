<?php

namespace App\Models;

use App\Models\Image;
use App\Models\Company;
use App\Models\Products;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryService extends Model
{
    use HasFactory;

    const CATEGORY_ERASER = 'Borrador';
    const CATEGORY_PUBLISH = 'Publicado';

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'status'
    ];

    public function isPublish(){
        return $this->status == CategoryService::CATEGORY_PUBLISH;
    }

    public function parent(){
        return $this->belongsTo(CategoryService::class, 'parent_id' );
    }

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }

    public function categoryServicesProducts(){
        return $this->belongsToMany(Products::class);
    }

    public function categoryServicesCompanies(){
        return $this->belongsToMany(Company::class);
    }
}
