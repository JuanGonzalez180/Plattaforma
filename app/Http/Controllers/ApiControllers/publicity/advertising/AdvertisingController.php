<?php

namespace App\Http\Controllers\ApiControllers\publicity\advertising;

use App\Http\Controllers\ApiControllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Advertising;
use App\Models\AdvertisingPlans;
use App\Models\Company;
use App\Models\Products;
use App\Models\Projects;
use App\Models\RegistrationPayments;
use App\Models\Tenders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class AdvertisingController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $rules = [
            'adv_type' => 'required',
            'date' => 'required',
            'hour' => 'required',
            'name' => 'required',
            'plan' => 'required|numeric',
        ];

        $this->validate($request, $rules);

        // Datos
        $advertisingFields['advertisingable_id'] = $request['adv_id'];

        if( $request['adv_type'] == 'products' ){
            $advertisingFields['advertisingable_type'] = Products::class;
            $product = Products::findOrFail($request['adv_id']);
            if( $product->company_id != $user->companyId() ){
                $productError = ['advertising' => 'Error, el producto no pertenece ha la compañía'];
                return $this->errorResponse($productError, 500);
            }
        }elseif( $request['adv_type'] == 'tenders' ){
            $advertisingFields['advertisingable_type'] = Tenders::class;
            $tender = Tenders::findOrFail($request['adv_id']);
            if( $tender->company_id != $user->companyId() ){
                $tenderError = ['advertising' => 'Error, La licitación no pertenece ha la compañía'];
                return $this->errorResponse($tenderError, 500);
            }
        }elseif( $request['adv_type'] == 'projects' ){
            $advertisingFields['advertisingable_type'] = Projects::class;
            $project = Projects::findOrFail($request['adv_id']);
            if( $project->company_id != $user->companyId() ){
                $projectError = ['advertising' => 'Error, El proyecto no pertenece ha la compañía'];
                return $this->errorResponse($projectError, 500);
            }
        }elseif( $request['adv_type'] == 'company' ){
            $advertisingFields['advertisingable_type'] = Company::class;
            $advertisingFields['advertisingable_id'] = $user->companyId();
        }

        if( $request['date'] && $request['hour']){
            $advertisingFields['date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day'])) . ' ' . $request['hour']['hour'] . ':' . $request['hour']['minute'];
        }

        $plan = AdvertisingPlans::findOrFail($request['plan']);
        $advertisingFields['plan_id'] = $plan->id;
        $advertisingFields['name'] = $request['name'];

        // Iniciar Transacción
        DB::beginTransaction();

        try {
            // Crear RegistroPago
            $registerFields['price'] = $plan->price;
            $registerFields['type'] = RegistrationPayments::TYPE_STRIPE;
            $registerFields['reference_payments'] = '';
            $registerFields['status'] = RegistrationPayments::REGISTRATION_PENDING;

            $registrationPayment = RegistrationPayments::create($registerFields);
            $advertisingFields['registration_payments_id'] = $registrationPayment->id;

            $advertising = Advertising::create($advertisingFields);
        } catch (\Throwable $th) {
            DB::rollBack();
            $advertisingError = ['advertising' => 'Error, no se ha podido crear el registro de la publicidad' . json_encode($th) ];
            return $this->errorResponse($advertisingError, 500);
        }
        
        DB::commit();
        return $this->showOne($advertising, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
