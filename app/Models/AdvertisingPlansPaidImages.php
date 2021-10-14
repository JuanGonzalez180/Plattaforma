<?php

namespace App\Models;

use App\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvertisingPlansPaidImages extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertisings_id',
        'adver_plans_images_id',
    ];

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }
}
