<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryProducts extends Model
{
    use HasFactory;

    protected $table = 'category_products';

    protected $fillable = [
        'categoty_id',
        'products_id'
    ];
}
