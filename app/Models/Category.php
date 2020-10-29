<?php

namespace App\Models;

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
}
