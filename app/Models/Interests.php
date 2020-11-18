<?php

namespace App\Models;

use App\Company;
use App\Products;
use App\Projects;
use App\Tenders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interests extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'type_id',
        'date',
        'date_update'
    ];

    public function company(){
        return $this->belongsToMany(Company::class);
    }

    public function products(){
        return $this->belongsToMany(Products::class);
    }

    public function projects(){
        return $this->belongsToMany(Projects::class);
    }

    public function tenders(){
        return $this->belongsToMany(Tenders::class);
    }
}
