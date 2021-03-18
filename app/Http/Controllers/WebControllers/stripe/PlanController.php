<?php

namespace App\Http\Controllers\WebControllers\stripe;

use App\Models\Plan;
use App\Models\Currency;
use App\Models\ProductStripe;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlanController extends Controller
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
        $plans = Plan::all();
        return view('plans.index', compact('plans'));
    }

    

    public function create(Request $request)
    {
        $plan = new Plan();
        $currencies = Currency::get();
        $products = ProductStripe::get();
        return view('plans.create', compact('plan', 'currencies', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $rules = [
            'name' => 'required',
            'currency' => 'required',
            'cost' => 'required|numeric|gt:0',
            'interval' => 'required',
            'interval_count' => 'required|numeric|gt:0',
            'product' => 'required',
        ];

        $this->validate( $request, $rules );

        $data = $request->except('_token');

        $price = $data['cost'] * 100;

        $product = ProductStripe::find($data['product']);

        //Stripe Plan Creation
        $stripePlanCreation = $this->stripe->plans->create([
            'amount' => $price,
            'currency' => $data['currency'],
            'interval' => $data['interval'],
            'interval_count' => $data['interval_count'],
            'product' => $product->stripe_product,
            'trial_period_days' => $data['days_trials'],
        ]);

        $data['iso'] = $data['currency'];
        $data['product_stripes_id'] = $product->id;
        $data['stripe_plan'] = $stripePlanCreation->id;

        Plan::create( $data );

        return redirect()->route('plans.index')->with('success', 'Plan creado satisfactoriamente');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $plan = Plan::findOrFail($id);
        $currencies = Currency::get();
        $products = ProductStripe::get();
        return view('plans.edit', compact('plan', 'currencies', 'products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        if( $request->days_trials ){
            $rules = [
                'days_trials' => 'numeric|gt:0'
            ];
            $this->validate( $request, $rules );
        }

        $plan = Plan::findOrFail($id);
        $this->stripe->plans->update(
            $plan->stripe_plan,
            ['trial_period_days' => $request->days_trials ? $request->days_trials : 0 ]
        );
        $plan->days_trials = $request->days_trials ? $request->days_trials : 0;
        $plan->save();

        return redirect()->route('plans.index')->with('success', 'Plan editado satisfactoriamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $plan = Plan::find($id);
        $this->stripe->plans->delete(
            $plan->stripe_plan,
            []
        );
        $plan->delete();

        $plans = Plan::all();
        return view('plans.index', compact('plans'));
    }

    /**
     * Show the Plan.
     *
     * @return mixed
     */
    /*public function show(Plan $plan, Request $request)
    {
        $paymentMethods = $request->user()->paymentMethods();

        $intent = $request->user()->createSetupIntent();
        
        return view('plans.show', compact('plan', 'intent'));
    }*/
}
