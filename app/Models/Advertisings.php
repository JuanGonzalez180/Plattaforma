<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Files;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Products;
use App\Models\Projects;
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

    const STATUS_START  = 'Sin iniciar';
    const STATUS_ACTIVE = 'Activo';
    const STATUS_ENDING = 'Terminado';

    const STATUS_ADMIN_CREATED  = 'Revisión';
    const STATUS_ADMIN_APPROVED = 'Aprovado';
    const STATUS_ADMIN_REJECTED = 'Rechazado';

    protected $fillable = [
        'advertisingable_id',
        'advertisingable_type',
        'plan_id',
        'name',
        'start_date',
        'start_time',
        'status'
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

    public function type_publicity_detail()
    {
        switch ($this->advertisingable_type) {
            case Company::class:
                $company = $this->payments->company;
                $row['type'] = 'Compañia';
                $row['name'] = $company->name;
                break;
            case Products::class:
                $product = Products::find($this->advertisingable_id);
                $row['type'] = 'Producto';
                $row['name'] = $product->name;
                break;
            case Projects::class:
                $project = Projects::find($this->advertisingable_id);
                $row['type'] = 'Proyecto';
                $row['name'] = $project->name;
                break;
            case Tenders::class:
                $project = Tenders::find($this->advertisingable_id);
                $row['type'] = 'Licitación';
                $row['name'] = $project->name;
                break;
        }

        return $row;
    }

    public function company()
    {
        return $this->payments->company->name;
    }

    public function type_publicity()
    {
        switch ($this->advertisingable_type) {
            case Company::class:
                $name = 'Compañia';
                break;
            case Products::class:
                $name = 'Producto';
                break;
            case Projects::class:
                $name = 'Proyecto';
                break;
        }

        return $name;
    }

    public function status_date()
    {
        $status = Advertisings::STATUS_START;
        if ($this->start_date && $this->start_time) {
            if (
                Carbon::now()->format('Y-m-d H:i') >= Carbon::parse($this->start_date . ' ' . $this->start_time)->format('Y-m-d H:i')
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
            ($this->status() == Advertisings::STATUS_START))
            ? true
            : false;
    }

    public function advertisingPlansPaidImages()
    {
        return $this->hasMany(AdvertisingPlansPaidImages::class);
    }
}
