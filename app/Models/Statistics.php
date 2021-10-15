<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    use HasFactory;

    const ACTION_CLICK  = 'Click';

    protected $fillable = [
        'statisticsable_type',
        'statisticsable_id',
        'action'
    ];

    public function statisticsable(){
        return $this->morphTo();
    }
}
