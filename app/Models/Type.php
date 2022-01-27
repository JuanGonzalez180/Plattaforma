<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
    use Sluggable;

    protected $fillable = [
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

    public function renameType()
    {
        switch ($this->name)
        {
            case 'Demanda':
                $name = 'Proyecto';
                break;
            case 'Oferta':
                $name = 'Proveedores';
                break;
            default:
                $name = "sin definir";
                break;
        }

        return $name;
    }

    public function typesEntity()
    {
        return $this->hasMany(TypesEntity::class);
    }
}
