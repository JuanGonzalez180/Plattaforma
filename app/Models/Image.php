<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'url',
        'size'
    ];

    protected $hidden = [
        'imageable_id',
        'imageable_type',
    ];

    public function imageable(){
        return $this->morphTo();
    }
}
