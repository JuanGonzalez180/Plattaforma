<?php

namespace App\Models;

use App\Files;
use App\Projects;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeProject extends Model
{
    use HasFactory;

    const TYPEPROJECT_ERASER = 'Borrador';
    const TYPEPROJECT_PUBLISH = 'Publicado';

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'status'
    ];

    public function isPublish(){
        return $this->status == TypeProject::TYPEPROJECT_PUBLISH;
    }

    public function parent(){
        return $this->belongsTo(TypeProject::class, 'parent_id' );
    }

    public function files(){
        return $this->belongsToMany(Files::class);
    }

    public function projects(){
        return $this->belongsToMany(Projects::class);
    }

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }
}
