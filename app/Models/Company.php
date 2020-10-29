<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type_entity_id',
        'nit',
        'country_id',
        'web',
        'image',
        'status',
        'date',
        'date_update'
    ];
}
