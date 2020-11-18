<?php

namespace App\Models;

use App\Company;
use App\Projects;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaData extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'type',
        'type_id',
        'date',
        'date_update'
    ];

    public function projects(){
        return $this->belongsTo(Projects::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }
}
