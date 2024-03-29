<?php

namespace App\Models;

use App\User;
use App\Models\Image;
use App\Models\Products;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\BrandsTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brands extends Model
{
    use HasFactory;

    public $transformer = BrandsTransformer::class;

    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'status',
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
    public function company(){
        return $this->belongsTo(Company::class);
    }
}

