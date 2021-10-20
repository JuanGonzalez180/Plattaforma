<?php

namespace App\Models;

use App\Models\Image;
use App\Models\AdvertisingPlansImages;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\AdvertisingPlansTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvertisingPlans extends Model
{
    use HasFactory;

    public $transformer = AdvertisingPlansTransformer::class;

    const RECTANGLE_TYPE   = 'Anuncio resultado de búsquedas';
    const SQUARE_TYPE      = 'Anuncio barra lateral izquierda';

    protected $fillable = [
        'name',
        'description',
        'days',
        'price',
        'type_ubication'
    ];

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }

    public function advertisingPlansImages(){
        return $this->hasMany(AdvertisingPlansImages::class);
    }

}
