<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'tagsable_id',
        'tagsable_type',
    ];
    
    public function tagsable(){
        return $this->morphTo();
    }
}
