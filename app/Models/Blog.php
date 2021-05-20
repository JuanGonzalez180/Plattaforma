<?php

namespace App\Models;

use App\Company;
use App\Files;
use App\User;
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

    public function files(){
        return $this->belongsToMany(Files::class);
    }
}
