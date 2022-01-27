<?php

namespace App\Models;

use App\Models\Company;
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
        'company_id',
        'price',
        'type',
        'reference_payments',
        'status',
        'paymentsable_id',
        'paymentsable_type'
    ];

    public function paymentsable(){
        return $this->morphTo();
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }
}
