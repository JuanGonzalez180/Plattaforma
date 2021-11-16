<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Files extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * type: Tipo de Archivo
     */
    protected $fillable = [
        'name',
        'type',
        'url',
        'size'
    ];

    protected $hidden = [
        'filesable_id',
        'filesable_type',
    ];

    public function filesable()
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
