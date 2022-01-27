<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagesAdvertisingPlans extends Model
{
    use HasFactory;

    const DESK_TYPE    = 'Escritorio';
    const TABLET_TYPE  = 'Tablet';
    const MOBILE_TYPE  = 'Movil';
  

    protected $fillable = [
        'name',
        'width',
        'high',
        'type'
    ];
}
