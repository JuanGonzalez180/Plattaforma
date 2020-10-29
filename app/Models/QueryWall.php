<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueryWall extends Model
{
    use HasFactory;

    const QUERYWALL_ERASER = 'Borrador';
    const QUERYWALL_PUBLISH = 'Publicado';

    protected $fillable = [
        'licitacion_id',
        'subject',
        'question',
        'answer',
        'user_id',
        'status',
        'date',
        'date_update'
    ];

    public function isPublish(){
        return $this->status == QueryWall::QUERYWALL_PUBLISH;
    }
}
