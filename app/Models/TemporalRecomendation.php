<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporalRecomendation extends Model
{
    use HasFactory;

    protected $table = 'temporal_recommendation';

    protected $fillable = [
        'company_id',
        'modelsable_id',
        'modelsable_type',
    ];

    // protected $hidden = [
    //     'modelsable_id',
    //     'modelsable_type',
    // ];

    public function filesable()
    {
        return $this->morphTo();
    }
}