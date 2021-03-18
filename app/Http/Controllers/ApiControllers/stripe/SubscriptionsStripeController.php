<?php

namespace App\Http\Controllers\ApiControllers\stripe;

use App\Models\Plan;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class SubscriptionsStripeController extends ApiController
{
    //
    protected $stripe;

    public function __construct() 
    {
        if( env('STRIPE_SECRET') ){
            $this->stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        }
    }

    

    /**
     * Create a new controller instance.
     *
     * @return void
    */
    public function __invoke( Request $request )
    {
        // return $request->email;
        $errorUser = false;
        try{
            $user = User::whereEmail($request->email)->first();
            if( !$user ){
                $errorUser = true;
            }
        } catch (\Throwable $th) {
            $errorUser = true;
        }

        if( $errorUser ){
            $userError = [ 'user' => 'Error, no se ha encontrado un usuario' ];
            return $this->errorResponse( $userError, 500 );
        }

        // Plans
        $plans = Plan::all();

        // Data
        $data = [
            'plans' => $plans,
            'intent' => $user->createSetupIntent()
        ];
        return $this->showOneData( $data, 200 );
    }

    public function store(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'plan' => 'required|numeric',
            'setupIntent' => 'required',
        ];
        $this->validate( $request, $rules );

        $errorUser = false;
        try{
            $user = User::whereEmail($request->email)->first();
            if( !$user ){
                $errorUser = true;
            }
        } catch (\Throwable $th) {
            $errorUser = true;
        }

        if( $errorUser ){
            $userError = [ 'user' => 'Error, no se ha encontrado un usuario' ];
            return $this->errorResponse( $userError, 500 );
        }

        $plan = Plan::findOrFail($request->plan);
        $paymentMethod = $request->setupIntent;
        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod($paymentMethod);
        $user->newSubscription('default', $plan->stripe_plan)
            ->trialDays($plan->days_trials)
            ->create($paymentMethod, [
                'email' => $user->email,
            ]);
        
        if( $user->company && $user->company[0] ){
            $company = $user->company[0];
            $company->status = Company::COMPANY_APPROVED;
            $company->save();
        }

        $data = ['success' => 'Se ha creado la suscripción satisfactoriamente'];
        return $this->showOneData( $data, 200 );
    }
}
