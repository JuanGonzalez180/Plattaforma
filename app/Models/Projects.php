<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    use HasFactory;

    const PRODUCT_ERASER = 'Borrador';
    const PRODUCT_PUBLISH = 'Publicado';

    protected $fillable = [
        'name',
        'company_id',
        'user_id',
        'description',
        'image',
        'images',
        'date_start',
        'date_start',
        'status',
        'date',
        'date_update'
    ];

    public function isPublish(){
        return $this->status == Products::PRODUCT_PUBLISH;
    }
}
