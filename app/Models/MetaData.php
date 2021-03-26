<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaData extends Model
{   
    protected $guarded = [];
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'metadatable_id',
        'metadatable_type',
    ];

    protected $hidden = [
        'metadatable_id',
        'metadatable_type',
    ];

    public function metadatable(){
        return $this->morphTo();
    }
}
