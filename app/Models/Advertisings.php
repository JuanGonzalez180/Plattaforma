<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Files;
use App\Models\AdvertisingPlans;
use App\Models\RegistrationPayments;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdvertisingPlansPaidImages;
use App\Transformers\AdvertisingsTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Advertisings extends Model
{
    use HasFactory;

    public $transformer = AdvertisingsTransformer::class;

    const STATUS_START = 'Sin iniciar';
    const STATUS_ACTIVE = 'Activo';
    const STATUS_ENDING = 'Terminado';

    protected $fillable = [
        'advertisingable_id',
        'advertisingable_type',
        'plan_id',
        'name',
        'start_date',
        'start_time'
    ];

    public function files()
    {
        return $this->morphMany(Files::class, 'filesable');
    }

    public function advertisingable()
    {
        return $this->morphTo();
    }

    public function Plan()
    {
        return $this->belongsTo(AdvertisingPlans::class);
    }

    public function status()
    {
        $status = Advertisings::STATUS_START;
        if ($this->start_date && $this->start_time) {
            if (
                Carbon::now()->format('Y-m-d H:i') >= $this->start_date . ' ' . $this->start_time
            ) {
                $status = Advertisings::STATUS_ACTIVE;
            }

            if (
                Carbon::now()->format('Y-m-d H:i') >= Carbon::parse($this->start_date . ' ' . $this->start_time)->addDays($this->plan->days)->format('Y-m-d H:i')
            ) {
                $status = Advertisings::STATUS_ENDING;
            }
        }

        return $status;
    }

    public function payments()
    {
        return $this->morphOne(RegistrationPayments::class, 'paymentsable');
    }

    public function can_post_publicity()
    {
        return (
            (in_array($this->payments->status, [RegistrationPayments::REGISTRATION_PENDING, RegistrationPayments::REGISTRATION_REJECTED]))
            ||
            ($this->status() == Advertisings::STATUS_START)
        )
        ? true
        : false;
    }

    public function advertisingPlansPaidImages()
    {
        return $this->hasMany(AdvertisingPlansPaidImages::class);
    }
}
