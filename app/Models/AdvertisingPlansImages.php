<?php

namespace App\Models;

use App\Models\AdvertisingPlans;
use App\Models\ImagesAdvertisingPlans;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvertisingPlansImages extends Model
{
    use HasFactory;

    // protected $table = 'advertising_plans_images';

    protected $fillable = [
        'advertising_plans_id',
        'images_advertising_plans_id'
    ];

    public function advertisingPlans(){
        return $this->belongsTo(AdvertisingPlans::class);
    }

    public function imagesAdvertisingPlans(){
        return $this->belongsTo(ImagesAdvertisingPlans::class);
    }
}
