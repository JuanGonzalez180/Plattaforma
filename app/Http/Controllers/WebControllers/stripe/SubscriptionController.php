<?php

namespace App\Http\Controllers\WebControllers\stripe;

use App\Models\User;
use App\Models\Currency;
use App\Models\Plan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{   
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
    public function index()
    {
        $plan = Plan::all()->first();
        return view('plans.subscriptions', compact('plan'));
    }

    public function create(Request $request, Plan $plan)
    {
        /*$plan = Plan::findOrFail($request->get('plan'));
        
        $user = $request->user();
        $paymentMethod = $request->paymentMethod;

        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod($paymentMethod);
        $user->newSubscription('default', $plan->stripe_plan)
            ->create($paymentMethod, [
                'email' => $user->email,
            ]);
        
        return redirect()->route('home')->with('success', 'Your plan subscribed successfully');*/
    }

}
