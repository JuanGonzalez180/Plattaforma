<?php

namespace App\Models;

use App\Blog;
use App\Category;
use App\Company;
use App\Products;
use App\Projects;
use App\TypeProject;
use App\TendersVersions;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * type: Tipo de Archivo
     */
    protected $fillable = [
        'name',
        'type',
        'url'
    ];

    protected $hidden = [
        'filesable_id',
        'filesable_type',
    ];
    
    public function filesable(){
        return $this->morphTo();
    }
}
