<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypesEntity extends Model
{
    use HasFactory;

    const ENTITY_ERASER = 'Borrador';
    const ENTITY_PUBLISH = 'Publicado';

    protected $fillable = [
        'type_id',
        'name',
        'status'
    ];

    public function isPublish(){
        return $this->status == TypesEntity::ENTITY_PUBLISH;
    }

    public function type(){
        return $this->belongsTo(Type::class);
    }

    public function companies(){
        return $this->hasMany(Company::class);
    }
}
