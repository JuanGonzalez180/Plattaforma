<?php

namespace App\Models;

use App\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    use Sluggable;

    protected $fillable = [
        'name',
        'alpha2Code'
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source'    => 'name',
                'onUpdate'  => true,
            ]
        ];
    }

    //Relacion Muchos a Muchos
    public function companies(){
        return $this->belongsToMany(Company::class);
    }
}
