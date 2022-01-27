<?php

namespace App\Http\Controllers\WebControllers\stripe;

use App\Models\ProductStripe;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductsStripeController extends Controller
{
    protected $stripe;

    public function __construct() 
    {
        if( env('STRIPE_SECRET') ){
            $this->stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        }
    }

    //
    /**
     * Create a new controller instance.
     *
     * @return void
    */
    public function index()
    {
        $products_stripe = ProductStripe::all();
        return view('products_stripe.index', compact('products_stripe'));
    }

    public function create()
    {
        //
        $products_stripe = new ProductStripe;
        return view('products_stripe.create', compact('products_stripe'));
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
            'name' => 'required'
        ];

        $this->validate( $request, $rules );

        $data = $request->except('_token');

        $stripeProduct = $this->stripe->products->create([
            'name' => $data['name'],
        ]);

        $data['stripe_product'] = $stripeProduct->id;

        $products_stripe = ProductStripe::create( $data );

        return redirect()->route('products_stripe.index')->with('success', 'Producto Stripe creada satisfactoriamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($stripe_product)
    {
        $product = ProductStripe::where('stripe_product', $stripe_product)->first();
        $this->stripe->products->delete(
            $stripe_product,
            []
        );
        $product->delete();

        $products_stripe = ProductStripe::all();
        return view('products_stripe.index', compact('products_stripe'));
    }
}
