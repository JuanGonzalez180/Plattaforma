<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypesEntity extends Model
{
    use HasFactory;

    const ENTITY_ERASER = 'Borrador';
    const ENTITY_PUBLISH = 'Publicado';

    const TYPE_DEMAND = 'Demanda';
    const TYPE_OFFER = 'Oferta';
    
    protected $fillable = [
        'name',
        'type',
        'status'
    ];

    public function isPublish(){
        return $this->status == TypesEntity::ENTITY_PUBLISH;
    }

    public function companies(){
        return $this->hasMany(Company::class);
    }
}
