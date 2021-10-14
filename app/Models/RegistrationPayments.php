<?php

namespace App\Models;

use App\Models\Advertising;
use App\Models\AdvertisingPlans;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistrationPayments extends Model
{
    use HasFactory;

    //Estado
    const REGISTRATION_PENDING   = 'Pendiente';
    const REGISTRATION_APPROVED  = 'Aprobado';
    const REGISTRATION_REJECTED  = 'Rechazado';

    //tipo
    const TYPE_STRIPE         = 'STRIPE';


    protected $fillable = [
        'price',
        'type',
        'reference_payments',
        'status'
    ];


}
