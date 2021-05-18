<?php

namespace App\Models;

use App\User;
use App\Models\Image;
use App\Models\Products;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\BrandsTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brands extends Model
{
    use HasFactory;

    public $transformer = BrandsTransformer::class;

    protected $fillable = [
        'user_id',
        'name',
        'status'
    ];

    const BRAND_ENABLED     = 'true';
    const BRAND_DISABLED    = 'false';

    public function user(){
        return $this->belongsTo(User::class);
    }

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }
}

