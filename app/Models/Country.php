<?php

namespace App\Models;

use App\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'alpha2Code'
    ];

    public function company(){
        return $this->hasMany(Company::class);
    }
}
