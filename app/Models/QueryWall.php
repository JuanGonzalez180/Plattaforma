<?php

namespace App\Models;

use App\Models\User;
use App\Models\Tenders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueryWall extends Model
{
    use HasFactory;

    const QUERYWALL_ERASER = 'Borrador';
    const QUERYWALL_PUBLISH = 'Publicado';

    protected $fillable = [
        'tender_id',
        'subject',
        'question',
        'answer',
        'user_id',
        'status'
    ];

    public function isPublish(){
        return $this->status == QueryWall::QUERYWALL_PUBLISH;
    }

    public function tenders(){
        return $this->belongsTo(Tenders::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
