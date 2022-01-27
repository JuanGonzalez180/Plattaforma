<?php

namespace App\Http\Controllers\ApiControllers\random;

use JWTAuth;
use DateTime;
use App\Models\Advertisings;
use App\Models\AdvertisingPlansPaidImages;
use App\Models\AdvertisingPlans;
use App\Models\RegistrationPayments;
use App\Models\TypesEntity;
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
        $user = $this->validateUser();
        $type_user  = $user->userType();

        switch ($request->type_ubication)
        {
            case AdvertisingPlans::RECTANGLE_TYPE:
                $type_ubication = AdvertisingPlans::RECTANGLE_TYPE;
                break;
            case AdvertisingPlans::SQUARE_TYPE:
                $type_ubication = AdvertisingPlans::SQUARE_TYPE;
                break;
        }
        
        $advertisings = Advertisings::select('advertisings.*', 'companies.slug')
            ->where('advertisings.status','=',Advertisings::STATUS_ADMIN_APPROVED)
            ->join('registration_payments','registration_payments.paymentsable_id','=','advertisings.id')
            ->join('advertising_plans','advertising_plans.id','=','advertisings.plan_id')
            ->where('advertising_plans.type_ubication','=',$type_ubication)
            ->where( DB::raw("DATE_FORMAT(advertisings.start_date, '%Y-%m-%d' )"),'<=', Carbon::now()->format('Y-m-d'))
            ->where( DB::raw("DATE_FORMAT(DATE_ADD(advertisings.start_date, INTERVAL +advertising_plans.days DAY), '%Y-%m-%d' )") ,'>=', Carbon::now()->format('Y-m-d'))
            ->where('registration_payments.paymentsable_type','=',Advertisings::class)

            //Company
            ->join('companies', 'companies.id', '=', 'registration_payments.company_id')
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.slug', '<>', $type_user)
            //EndCompany

            ->whereIn('registration_payments.status',[RegistrationPayments::REGISTRATION_APPROVED])
            ->orderByRaw('rand()')
            ->take(6)
            ->get();
        
        // $viewAction = $advertisings->first();
        // if( $viewAction ){
        //     $viewAction->addStatistics( 'view' );
        // }
        
        // Este transformer hace que se aumente a 1 la estadística View en la Base de datos.
        $transformer = Advertisings::TRANSFORMER_ADVERTISING_RANDOM;

        return $this->showAllPaginateSetTransformer($advertisings, $transformer);
    }

}