<?php

namespace App\Models;

use App\Models\Files;
use App\Models\AdvertisingPlans;
use App\Models\RegistrationPayments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Transformers\AdvertisingsTransformer;
use Carbon\Carbon;

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

    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }

    public function advertisingable(){
        return $this->morphTo();
    }

    public function Plan(){
        return $this->belongsTo(AdvertisingPlans::class);
    }

    public function status(){
        $status = Advertisings::STATUS_START;
        if( $this->start_date && $this->start_time  ){
            if( 
                Carbon::now()->format('Y-m-d H:i') >= $this->start_date . ' ' . $this->start_time
            ){
                $status = Advertisings::STATUS_ACTIVE;
            }

            if( 
                Carbon::now()->format('Y-m-d H:i') >= Carbon::parse($this->start_date . ' ' . $this->start_time)->addDays($this->plan->days)->format('Y-m-d H:i')
            ){
                $status = Advertisings::STATUS_ENDING;
            }
        }
        
        return $status;
    }

    public function payments(){
        return $this->morphOne(RegistrationPayments::class, 'paymentsable');
    }
}
