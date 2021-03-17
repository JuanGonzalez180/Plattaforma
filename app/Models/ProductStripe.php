<?php

namespace App\Models;

use App\Plan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class ProductStripe extends Model
{
    use HasFactory, Sluggable;
    
    protected $fillable = [
        'name',
        'stripe_product'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source'    => 'name',
                'onUpdate'  => true,
            ]
        ];
    }

    public function plan(){
        return $this->hasMany(Plan::class);
    }
}
