<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialNetworks extends Model
{
    use HasFactory;

    const SOCIAL_ERASER = 'Borrador';
    const SOCIAL_PUBLISH = 'Publicado';

    protected $fillable = [
        'name',
        'icono',
        'status'
    ];

    public function isPublish(){
        return $this->status == SocialNetworks::SOCIAL_PUBLISH;
    }
}
