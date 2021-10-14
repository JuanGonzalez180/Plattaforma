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
        'registration_payments_id',
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

    public function registrationPayments(){
        return $this->belongsTo(RegistrationPayments::class);
    }
}
