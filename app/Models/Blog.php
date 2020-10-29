<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    const BLOG_ERASER = 'Borrador';
    const BLOG_PUBLISH = 'Publicado';

    protected $fillable = [
        'name',
        'image',
        'description_short',
        'description',
        'status',
        'user_id',
        'date',
        'date_update',
        'company_id'
    ];

    public function isPublish(){
        return $this->status == Blog::BLOG_PUBLISH;
    }
}
