<?php

namespace App\Models;

use App\Models\Image;
use App\Models\Files;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\CatalogTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Catalogs extends Model
{
    use HasFactory;

    const CATALOG_ERASER  = 'Borrador';
    const CATALOG_PUBLISH = 'Publicado';

    public $transformer = CatalogTransformer::class;

    protected $fillable = [
        'name',
        'description_short',
        'description',
        'status',
        'user_id',
        'company_id'
    ];

    public function isPublish(){
        return $this->status == Catalogs::CATALOG_PUBLISH;
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }

    // Relacion uno a muchos polimorfica
    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }
}
