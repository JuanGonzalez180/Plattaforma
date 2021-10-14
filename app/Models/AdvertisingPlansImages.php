<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisingPlansImages extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertising_plans_id',
        'images_advertising_plans_id'
    ];
}
