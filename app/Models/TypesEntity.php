<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypesEntity extends Model
{
    use HasFactory;

    const TYPEENTITY_ERASER = 'Borrador';
    const TYPEENTITY_PUBLISH = 'Publicado';
    
    protected $fillable = [
        'name',
        'type',
        'status'
    ];

    public function isPublish(){
        return $this->status == TypesEntity::TYPEENTITY_PUBLISH;
    }
}
