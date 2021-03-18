<?php

namespace App\Models;

use App\Company;
use App\Projects;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    protected $guarded = [];

    use HasFactory;

    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'address',
        'latitud',
        'longitud',
        'zoom'
    ];

    protected $hidden = [
        'addressable_id',
        'addressable_type',
    ];

    public function addressable(){
        return $this->morphTo();
    }
}
