<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendersVersions extends Model
{
    use HasFactory;

    protected $fillable = [
        'adenda',
        'licitacion_id',
        'precio',
        'numero',
        'unique_id',
        'date_start',
        'date_end',
        'date',
        'date_update'
    ];
}
