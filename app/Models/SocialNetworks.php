<?php

namespace App\Models;

use App\Company;
use App\Projects;
use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialNetworks extends Model
{
    use HasFactory;

    const SOCIAL_ERASER = 'Borrador';
    const SOCIAL_PUBLISH = 'Publicado';

    protected $fillable = [
        'name',
        'status'
    ];

    public function isPublish(){
        return $this->status == SocialNetworks::SOCIAL_PUBLISH;
    }
    
    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }

    /*public function company(){
        return $this->belongsToMany(Company::class);
    }

    public function projects(){
        return $this->belongsToMany(Projects::class);
    }*/
}
