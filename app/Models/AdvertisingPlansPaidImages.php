<?php

namespace App\Models;

use App\Models\Image;
use App\Models\Advertisings;
use App\Models\AdvertisingPlansImages;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\AdvertisingPlansPaidImagesTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvertisingPlansPaidImages extends Model
{
    use HasFactory;

    public $transformer = AdvertisingPlansPaidImagesTransformer::class;

    protected $fillable = [
        'advertisings_id',
        'adver_plans_images_id',
    ];

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }

    public function advertisings(){
        return $this->belongsTo(Advertisings::class);
    }

    public function advertisingPlansImages(){
        return $this->belongsTo(AdvertisingPlansImages::class);
    }
}
