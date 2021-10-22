<?php

namespace App\Http\Controllers\ApiControllers\random;

use JWTAuth;
use App\Models\Advertisings;
use App\Models\AdvertisingPlansPaidImages;
use App\Models\AdvertisingPlans;
use App\Models\RegistrationPayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;
use Carbon\Carbon;

class RandomAdvertisingsController extends ApiController
{

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function __invoke(Request $request)
    {
        $advertisings = Advertisings::select('advertisings.*')
        ->where(DB::raw("CONCAT(advertisings.start_date,'',advertisings.start_time)"),'<=',Carbon::now()->format('Y-m-d H:i'))
        ->join('registration_payments','registration_payments.paymentsable_id','=','advertisings.id')
        ->where('registration_payments.paymentsable_type','=',Advertisings::class)
        ->whereIn('registration_payments.status',[RegistrationPayments::REGISTRATION_PENDING,RegistrationPayments::REGISTRATION_REJECTED])
        ->orderByRaw('rand()')
        ->take(10)
        ->get();

        return $this->showAllPaginate($advertisings);
    }

}
