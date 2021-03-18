<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialNetworksRelation extends Model
{
    protected $guarded = [];
    
    protected $fillable = [
        'socialable_id',
        'socialable_type',
        'social_networks_id',
        'link'
    ];

    protected $hidden = [
        'socialable_id',
        'socialable_type',
    ];
    
    use HasFactory;

    public function socialable(){
        return $this->morphTo();
    }
}
