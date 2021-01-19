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
        'icon',
        'image',
        'parent_id',
        'status',
        'date',
        'date_update'
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
}
