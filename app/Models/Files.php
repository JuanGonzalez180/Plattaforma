<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    use HasFactory;

    /**
     * type: Tipo de Archivo
     */
    protected $fillable = [
        'name',
        'type',
        'extension',
        'date',
        'date_update'
    ];
}
