<?php

namespace App\Http\Controllers\ApiControllers\publicity\advertising;

use App\Http\Controllers\ApiControllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Advertisings;
use App\Models\AdvertisingPlans;
use App\Models\Company;
use App\Models\Products;
use App\Models\Projects;
use App\Models\RegistrationPayments;
use App\Models\Tenders;
use App\Models\Statistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use JWTAuth;

class AdvertisingController extends ApiController
{
    public $routeFile = 'public/';
    public $routeAdvertisings = 'images/advertisings/';
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
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $companyID = $user->companyId();

        $advertisings = Advertisings::select("advertisings.*")
                            ->join('registration_payments AS payments', function($join) use($companyID) {
                                $join->on('payments.paymentsable_id', '=', 'advertisings.id');
                                $join->where('payments.paymentsable_type', Advertisings::class );
                                $join->where('payments.company_id', '=', $companyID );
                            })
                            ->orderBy('advertisings.id', 'desc')
                            ->get();

        return $this->showAllPaginate($advertisings);
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
        $companyId = $user->companyId();
        $advertisingFields['advertisingable_id'] = $request['adv_id'];

        if( $request['adv_type'] == 'products' ){
            $advertisingFields['advertisingable_type'] = Products::class;
            $product = Products::findOrFail($request['adv_id']);
            if( $product->company_id != $companyId ){
                $productError = ['advertising' => 'Error, el producto no pertenece ha la compañía'];
                return $this->errorResponse($productError, 500);
            }
        }elseif( $request['adv_type'] == 'tenders' ){
            $advertisingFields['advertisingable_type'] = Tenders::class;
            $tender = Tenders::findOrFail($request['adv_id']);
            if( $tender->company_id != $companyId ){
                $tenderError = ['advertising' => 'Error, La licitación no pertenece ha la compañía'];
                return $this->errorResponse($tenderError, 500);
            }
        }elseif( $request['adv_type'] == 'projects' ){
            $advertisingFields['advertisingable_type'] = Projects::class;
            $project = Projects::findOrFail($request['adv_id']);
            if( $project->company_id != $companyId ){
                $projectError = ['advertising' => 'Error, El proyecto no pertenece ha la compañía'];
                return $this->errorResponse($projectError, 500);
            }
        }elseif( $request['adv_type'] == 'company' ){
            $advertisingFields['advertisingable_type'] = Company::class;
            $advertisingFields['advertisingable_id'] = $companyId;
        }

        if( $request['date'] ){
            $advertisingFields['start_date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
        }
        if( $request['hour'] ){
            // $advertisingFields['start_time'] = $request['hour']['hour'] . ':' . $request['hour']['minute'];
        }

        $plan = AdvertisingPlans::findOrFail($request['plan']);
        $advertisingFields['plan_id'] = $plan->id;
        $advertisingFields['name'] = $request['name'];

        // Saber el número de imágenes que debe subir.
        $plan->advertisingPlansImages = $plan->advertisingPlansImagesApprove();
        if(count($plan->advertisingPlansImages) !== count($request->images)){
            $projectError = ['advertising' => 'Error, Debes subir la misma cantidad de imágenes'];
            return $this->errorResponse($projectError, 500);
        }

        // Iniciar Transacción
        DB::beginTransaction();

        try {
            // Crear RegistroPago
            $advertising = Advertisings::create($advertisingFields);

            $registerFields['price'] = $plan->price;
            $registerFields['type'] = RegistrationPayments::TYPE_STRIPE;
            $registerFields['reference_payments'] = '';
            $registerFields['status'] = RegistrationPayments::REGISTRATION_PENDING;
            $registerFields['paymentsable_id'] = $advertising->id;
            $registerFields['paymentsable_type'] = Advertisings::class;
            $registerFields['company_id'] = $companyId;

            $registrationPayment = RegistrationPayments::create($registerFields);

            // Subir imágenes
            foreach ($request->images as $key => $image) {
                if ($image["image"]) {
                    // $paidImage = $advertising->advertisingPlansPaidImages->where('adver_plans_images_id', $image["id"]);
                    // if( $paidImage->count() ){
                        // $paidImage = $paidImage->first();
                    // } else {
                    // }
                    $paidImage = $advertising->advertisingPlansPaidImages()->create([
                        'adver_plans_images_id' => $image["id"]
                    ]);
    
                    $png_url    = "advertising-" . $paidImage->id . "-" . time() . ".jpg";
                    $img        = $image["image"];
                    $img        = substr($img, strpos($img, ",") + 1);
                    $data       = base64_decode($img);
    
                    $routeFile = $this->routeAdvertisings . $advertising->id . '/' . $png_url;
    
                    Storage::disk('local')->put($this->routeFile . $routeFile, $data);
                    if ($paidImage->image) {
                        Storage::disk('local')->delete($this->routeFile . $paidImage->image->url);
                        $paidImage->image()->update(['url' => $routeFile]);
                    } else {
                        Storage::disk('local')->put($this->routeFile . $routeFile, $data);
                        $paidImage->image()->create(['url' => $routeFile]);
                    }
                }
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            $advertisingError = ['advertising' => 'Error, no se ha podido crear el registro de la publicidad' ];
            return $this->errorResponse($advertisingError, 500);
        }
        
        DB::commit();
        return $this->showOne($advertising, 201);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = $this->validateUser();

        $advertisings = Advertisings::find($id);
        $advertisings->status = $advertisings->status_date();
        if( $advertisings->advertisingable_type == Products::class ){
            $advertisings->typeItem = Products::findOrFail($advertisings->advertisingable_id);
            $advertisings->typeItem->image;
        }elseif( $advertisings->advertisingable_type == Tenders::class ){
            $advertisings->typeItem = Tenders::findOrFail($advertisings->advertisingable_id);
        }elseif( $advertisings->advertisingable_type == Projects::class ){
            $advertisings->typeItem = Projects::findOrFail($advertisings->advertisingable_id);
            $advertisings->typeItem->image;
        }elseif( $advertisings->advertisingable_type == Company::class ){
            $advertisings->typeItem = Company::findOrFail($advertisings->advertisingable_id);
            $advertisings->typeItem->image;
        }

        $advertisings->payments;
        $advertisings->plan;

        $advertisings->advertisingPlansPaidImages;

        foreach($advertisings->advertisingPlansPaidImages as $value){
            $value->image;
            $value->advertisingPlansImages->imagesAdvertisingPlans;
        }

        // Statistics
        $statistic = [];

        $statistic['click'] = Statistics::selectRaw("COUNT(*) clicks, DATE_FORMAT(created_at, '%Y-%m-%d') date")
                            ->where( 'statisticsable_type', $advertisings->advertisingable_type )
                            ->where( 'statisticsable_id', $advertisings->advertisingable_id )
                            ->where( 'action', 'click' )
                            ->groupBy('date')
                            ->get();

        $statistic['view'] = Statistics::selectRaw("COUNT(*) views, DATE_FORMAT(created_at, '%Y-%m-%d') date")
                            ->where( 'statisticsable_type', $advertisings->advertisingable_type )
                            ->where( 'statisticsable_id', $advertisings->advertisingable_id )
                            ->where( 'action', 'view' )
                            ->groupBy('date')
                            ->get();

        $advertisings->statistic = $statistic;

        return $this->showOne($advertisings, 200);
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
        $user = $this->validateUser();
        
        $rules = [
            'name' => 'required',
            'date' => 'required',
            'hour' => 'required'
        ];
        
        $this->validate($request, $rules);

        // Iniciar Transacción
        DB::beginTransaction();

        if( $request['date'] ){
            $advertisingFields['start_date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
        }
        if( $request['hour'] ){
            // $advertisingFields['start_time'] = $request['hour']['hour'] . ':' . $request['hour']['minute'];
        }

        try {
            $advertisings = Advertisings::find($id);
            $advertisings->name = $request->name;
            $advertisings->start_date = $advertisingFields['start_date'];
            // $advertisings->start_time = $advertisingFields['start_time'];

            $advertisings->save();

        } catch (\Throwable $th) {
            DB::rollBack();
            $advertisingError = ['advertising' => 'Error, no se ha podido editar el registro de la publicidad' . json_encode($th) ];
            return $this->errorResponse($advertisingError, 500);
        }

        DB::commit();

        return $this->showOne($advertisings, 201);
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
