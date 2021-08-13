<?php

namespace App\Models;

use App\Models\User;
use App\Models\Files;
use App\Models\Company;
use App\Models\Image;
use App\Transformers\BlogTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Blog extends Model
{
    use HasFactory;

    const BLOG_ERASER = 'Borrador';
    const BLOG_PUBLISH = 'Publicado';

    public $transformer = BlogTransformer::class;

    protected $fillable = [
        'name',
        'description_short',
        'description',
        'status',
        'user_id',
        'company_id'
    ];

    public function isPublish(){
        return $this->status == Blog::BLOG_PUBLISH;
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }

    // Relacion uno a muchos polimorfica
    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }

}
