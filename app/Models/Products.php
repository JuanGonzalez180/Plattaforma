<?php

namespace App\Models;

use App\Models\User;
use App\Models\Image;
use App\Models\Brands;
use App\Models\Tags;
use App\Models\Files;
use App\Models\Company;
use App\Models\Remarks;
use App\Models\Category;
use App\Models\Interests;
use App\Models\Notifications;
use App\Models\CategoryService;
use App\Transformers\ProductsTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Products extends Model
{
    use HasFactory;

    const PRODUCT_ERASER = 'Borrador';
    const PRODUCT_PUBLISH = 'Publicado';
    const USER_DEFAULT = 1;

    const TYPE_PRODUCT = 'Producto';
    const TYPE_SERVICE = 'Servicio';
    const TYPE_BRAND = 'Marca';

    public $transformer = ProductsTransformer::class;

    protected $fillable = [
        'name',
        'company_id',
        'user_id',
        'brand_id',
        'description',
        'type',
        'status'
    ];

    public function isPublish(){
        return $this->status == Products::PRODUCT_PUBLISH;
    }

    public function type(){
        return $this->type;
    }
    
    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function brand(){
        return $this->belongsTo(Brands::class);
    }

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }

    public function productCategories(){
        return $this->belongsToMany(Category::class);
    }

    public function productCategoryServices(){
        return $this->belongsToMany(CategoryService::class);
    }

    // Relacion uno a muchos polimorfica
    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }

    // Relacion uno a muchos polimorfica
    public function tags(){
        return $this->morphMany(Tags::class, 'tagsable');
    }

    // Relacion uno a muchos polimorfica
    public function notifications(){
        return $this->morphMany(Notifications::class, 'notificationsable');
    }

    // Relacion uno a muchos polimorfica
    public function remarks(){
        return $this->morphMany(Remarks::class, 'remarksable');
    }

    // Relacion uno a muchos polimorfica
    public function interests(){
        return $this->morphMany(Interests::class, 'interestsable');
    }
}
