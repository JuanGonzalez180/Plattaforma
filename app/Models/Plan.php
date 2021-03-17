<?php

namespace App\Models;

use App\ProductStripe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'name',
        'stripe_plan',
        'cost',
        'description',
        'interval',
        'interval_count',
        'iso',
        'product_stripes_id',
        'days_trials'
    ];

    protected $hidden = [
        'stripe_plan',
        'product_stripes_id',
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

    public function product_stripes(){
        return $this->belongsTo(ProductStripe::class);
    }
}
