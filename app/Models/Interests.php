<?php

namespace App\Models;

use App\Models\User;
use App\Transformers\InterestsTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interests extends Model
{
    protected $guarded = [];

    use HasFactory;

    public $transformer = InterestsTransformer::class;

    protected $fillable = [
        'user_id',
        'interestsable_id',
        'interestsable_type'
    ];

    protected $hidden = [
        'interestsable_id',
        'interestsable_type',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function interestsable(){
        return $this->morphTo();
    }
}
