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

    public function imageable()
    {
        return $this->morphTo();
    }

    public function formatSize()
    {
        if (round(($this->size / pow(1024, 2)), 3) < '1') {
            $file = $this->size . ' bites';
        } else if (round(($this->size / pow(1024, 2)), 3) < '1024') {
            $file = round(($this->size / pow(1024, 2)), 3) . ' MB';
        } else if (round(($this->size / pow(1024, 2)), 3) >= '1024') {
            $file = round(($this->size / pow(1024, 2)), 3) . ' GB';
        }

        return $file;
    }
}
