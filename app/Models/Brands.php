<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'status'
    ];

    const BRAND_ENABLED     = 'true';
    const BRAND_DISABLED    = 'false';

    public function user(){
        return $this->belongsTo(User::class);
    }
}
