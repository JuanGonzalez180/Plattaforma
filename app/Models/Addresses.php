<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'type',
        'type_id',
        'latitud',
        'longitud',
        'zoom',
        'date',
        'date_update'
    ];
}
