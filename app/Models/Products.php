<?php

namespace App\Models;

use App\Category;
use App\Company;
use App\Files;
use App\Interests;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    const PRODUCT_ERASER = 'Borrador';
    const PRODUCT_PUBLISH = 'Publicado';

    const TYPE_PRODUCT = 'Producto';
    const TYPE_SERVICE = 'Servicio';
    const TYPE_BRAND = 'Marca';

    protected $fillable = [
        'name',
        'company_id',
        'user_id',
        'description',
        'type',
        'image',
        'images',
        'status',
        'date',
        'date_update'
    ];

    public function isPublish(){
        return $this->status == Products::PRODUCT_PUBLISH;
    }

    public function type(){
        return $this->type;
    }
    
    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function files(){
        return $this->belongsToMany(Files::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function interests(){
        return $this->belongsToMany(Interests::class);
    }
}
