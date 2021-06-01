<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Files extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * type: Tipo de Archivo
     */
    protected $fillable = [
        'filesable_id',
        'filesable_type',
        'name',
        'type',
        'url'
    ];

    protected $hidden = [
        // 'filesable_id',
        // 'filesable_type',
    ];
    
    public function filesable(){
        return $this->morphTo();
    }
}
