<?php

namespace App\Models;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlatform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }
}
