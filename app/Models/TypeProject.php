<?php

namespace App\Models;

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
        'icon',
        'image',
        'parent_id',
        'status',
        'date',
        'date_update'
    ];

    public function isPublish(){
        return $this->status == TypeProject::TYPEPROJECT_PUBLISH;
    }
}
