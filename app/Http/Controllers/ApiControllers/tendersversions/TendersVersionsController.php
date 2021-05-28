<?php

namespace App\Http\Controllers\ApiControllers\tendersversions;

use JWTAuth;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersVersionsController extends ApiController
{

    public function validateUser(){
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $tender_id = $request->tender_id;
 

        $lastVersion = TendersVersions::where('tenders_id','=', $tender_id)
            ->orderBy('created_at','DESC')
            ->get()
            ->first();

        if($lastVersion->status == TendersVersions::LICITACION_PUBLISH) {

            $rules = [
                'adenda'    => 'required',
                'price'     => 'required|numeric',
                'project'   => 'required|numeric',
                'date'      => 'required',
                'hour'      => 'required'
            ];

            // Iniciar Transacción
            DB::beginTransaction();

            $tenderVersionFields['adenda']  = $request['adenda'];
            $tenderVersionFields['price']   = $request['price'];

            if( $request['date'] ){
                $tenderVersionFields['date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
            }
            
            if( $request['hour'] ){
                $tenderVersionFields['hour'] = $request['hour']['hour'] . ':' . $request['hour']['minute'];
            }

            $tenderVersionFields['tenders_id']  = $tender_id;
            $tenderVersionFields['status']      = TendersVersions::LICITACION_PUBLISH;

            try{
                $tendersVersions                = TendersVersions::create( $tenderVersionFields );
                // Crear TenderVersion
            } catch (\Throwable $th) {
                // Si existe algún error al momento de crear el usuario
                $errorTender = true;
                DB::rollBack();
                $tenderError = [ 'tenderVersion' => 'Error, no se ha podido crear la versión del tenders'];
                return $this->errorResponse( $tenderError, 500 );
            }

            DB::commit();

            return $this->showOne($tendersVersions,201);

        }else{
            $tenderError = [ 'tenderVersion' => 'Error, la ultima versión de la licitacion no esta publicada'];
            return $this->errorResponse( $tenderError, 500 );
        }

        return [];
        
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
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


    public function edit($id)
    {
        $lastVersion = TendersVersions::where('tenders_id','=', $id)
            ->orderBy('created_at','DESC')
            ->get()
            ->first();

        $lastVersion->id;
        $lastVersion->adenda;
        $lastVersion->price;
        $lastVersion->status;
        $lastVersion->date;
        $lastVersion->hour;

        return $this->showOne($lastVersion,201);
    }
}
