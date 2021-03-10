<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $guarded = [];
    
    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'url'
    ];

    protected $hidden = [
        'imageable_id',
        'imageable_type',
    ];

    use HasFactory;

    public function imageable(){
        return $this->morphTo();
    }
}
