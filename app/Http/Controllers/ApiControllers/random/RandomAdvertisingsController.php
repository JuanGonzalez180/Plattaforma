<?php

namespace App\Http\Controllers\ApiControllers\random;

use JWTAuth;
use DateTime;
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
        //filtrar por oferta y demanda (usuario logueado)
        //type = debe terner encuenta si el  == 

        // $AdvertisingPlans

        switch ($request->type_ubication)
        {
            case AdvertisingPlans::RECTANGLE_TYPE:
                $type_ubication = AdvertisingPlans::RECTANGLE_TYPE;
                break;
            case AdvertisingPlans::SQUARE_TYPE:
                $type_ubication = AdvertisingPlans::SQUARE_TYPE;
                break;
        }
        
        $advertisings = Advertisings::select('advertisings.*')
            ->where('advertisings.status','=',Advertisings::STATUS_ADMIN_APPROVED)
            ->join('registration_payments','registration_payments.paymentsable_id','=','advertisings.id')
            ->join('advertising_plans','advertising_plans.id','=','advertisings.plan_id')
            ->where('advertising_plans.type_ubication','=',$type_ubication)
            ->where( DB::raw("DATE_FORMAT(CONCAT(advertisings.start_date,' ',advertisings.start_time), '%Y-%m-%d %H:%i' )"),'<=', Carbon::now()->format('Y-m-d H:i'))
            ->where( DB::raw("DATE_FORMAT(CONCAT(DATE_ADD(advertisings.start_date, INTERVAL +advertising_plans.days DAY),' ',advertisings.start_time), '%Y-%m-%d %H:%i' )") ,'>=', Carbon::now()->format('Y-m-d H:i'))
            ->where('registration_payments.paymentsable_type','=',Advertisings::class)
            ->whereIn('registration_payments.status',[RegistrationPayments::REGISTRATION_APPROVED])
            ->orderByRaw('rand()')
            ->take(6)
            ->get();

        // Carbon::now()->addDays("advertising_plans.days")->format('Y-m-d H:i');

        return $this->showAllPaginate($advertisings);
    }

}