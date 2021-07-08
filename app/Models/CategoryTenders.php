<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tenders;
use Illuminate\Database\Eloquent\Model;

class CategoryTenders extends Model
{
    use HasFactory;

    protected $table = 'category_tenders';

    protected $fillable = [
        'categoty_id',
        'tenders_id'
    ];

}
