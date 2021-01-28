<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class TypesEntity extends Model
{
    use HasFactory;
    use Sluggable;

    const ENTITY_ERASER = 'Borrador';
    const ENTITY_PUBLISH = 'Publicado';

    protected $fillable = [
        'type_id',
        'name'
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

    public function isPublish(){
        return $this->status == TypesEntity::ENTITY_PUBLISH;
    }

    public function type(){
        return $this->belongsTo(Type::class);
    }

    public function companies(){
        return $this->hasMany(Company::class);
    }
}
