<?php

namespace App\Models;

use App\Models\Files;
use App\AdvertisingPlans;
use App\RegistrationPayments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Advertisings extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertisingable_id',
        'advertisingable_type',
        'plan_id',
        'name'
    ];

    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }

    public function advertisingable(){
        return $this->morphTo();
    }

    public function Plan(){
        return $this->belongsTo(AdvertisingPlans::class);
    }

    public function payments(){
        return $this->morphMany(RegistrationPayments::class, 'paymentsable');
    }
}
