<?php

namespace App\Models;

use App\Files;
use App\Products;
use App\Tenders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
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
        return $this->status == Category::CATEGORY_PUBLISH;
    }

    public function parent(){
        return $this->belongsTo(Category::class, 'parent_id' );
    }

    public function files(){
        return $this->belongsToMany(Files::class);
    }

    public function tenders(){
        return $this->belongsToMany(Tenders::class);
    }

    public function products(){
        return $this->belongsToMany(Products::class);
    }
    
    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }
}
