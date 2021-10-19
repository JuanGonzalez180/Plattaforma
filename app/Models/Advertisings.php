<?php

namespace App\Models;

use App\Models\Files;
use App\Models\AdvertisingPlans;
use App\Models\RegistrationPayments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Transformers\AdvertisingsTransformer;

class Advertisings extends Model
{
    use HasFactory;

    public $transformer = AdvertisingsTransformer::class;

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
