<?php

namespace App\Http\Controllers\ApiControllers\tendersversions;

use JWTAuth;
use App\Models\Files;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
        $tender_id  = $request->tender_id;
        $files      = $request['files'];
 
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
            $tenderVersionFields['status']      = TendersVersions::LICITACION_CREATED;

            try{
                $tendersVersions                = TendersVersions::create( $tenderVersionFields );

                foreach ($request->tags as $key => $tag) {
                    $tendersVersions->tags()->create(['name' => $tag['displayValue']]);
                }
                // Crear TenderVersion
            } catch (\Throwable $th) {
                // Si existe algún error al momento de crear el usuario
                $errorTender = true;
                DB::rollBack();
                $tenderError = [ 'tenderVersion' => 'Error, no se ha podido crear la versión de la licitación'];
                return $this->errorResponse( $tenderError, 500 );
            }

            if( $tendersVersions ) {
                
                $id_old     = $lastVersion->id;
                $id_last    = $tendersVersions->id;
                
                if( $files ){
                    foreach ($files as $key => $file) {
                        // Files::select('filesable_type','name','type','url')->where('id',14)->get()->first()
                        $oldVersion = Files::select('filesable_type','name','type','url')
                                            ->where('id',$file['files_id'])
                                            ->get()
                                            ->first();

                        $file_name = $oldVersion->name;
                        $carpeta = "images/tenders/".$id_last."/documents";
        
                        $file_url       = storage_path('app/public/'.$oldVersion->url); 
                        $file_url_last  = storage_path('app/public/'.$carpeta.'/'.$file_name); 
        
                        if (!File::exists(storage_path('app/public/'.$carpeta))){
                            File::makeDirectory(storage_path('app/public/'.$carpeta), 777, true);
                        }
        
                        File::copy($file_url, $file_url_last);
                        $tendersVersions->files()->create([ 'name' => $oldVersion->name, 'type'=> $oldVersion->type, 'url' => $carpeta.'/'.$file_name]);
                    }
                }

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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $lastVersion = TendersVersions::where('tenders_id','=', $id)
            ->orderBy('created_at','DESC')
            ->get()
            ->first();

        if($lastVersion->status == TendersVersions::LICITACION_CREATED) {
            $rules = [
                'adenda'    => 'required',
                'price'     => 'required|numeric',
                'date'      => 'required',
                'hour'      => 'required'
            ];

            // Iniciar Transacción
            DB::beginTransaction();

            $lastVersion->adenda  = $request['adenda'];
            $lastVersion->price   = $request['price'];

            
            if( $request['date'] ){
                $lastVersion->date = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
            }
            
            if( $request['hour'] ){
                $lastVersion->hour = $request['hour']['hour'] . ':' . $request['hour']['minute'];
            }
            
            $lastVersion->status      = TendersVersions::LICITACION_CREATED;
            
            try{
                $lastVersion->save();

                // Tags
                // Eliminar los anteriores
                foreach( $lastVersion->tags as $key => $tag ){
                    $tag->delete();
                }

                foreach ($request->tags as $key => $tag) {
                    $lastVersion->tags()->create(['name' => $tag['displayValue']]);
                }

                // Axtualiza TenderVersion
            } catch (\Throwable $th) {
                // Si existe algún error al momento de crear el usuario
                $errorTender = true;
                DB::rollBack();
                $tenderError = [ 'tenderVersion' => 'Error, no se ha podido actulizar la versión de la licitación'];
                return $this->errorResponse( $tenderError, 500 );
            }
            
            DB::commit();
            $lastVersion->tags;
            return $this->showOne($lastVersion,201);

        }else{
            $tenderError = [ 'tenderVersion' => 'Error, la ultima versión de la licitacion no esta Borrador'];
            return $this->errorResponse( $tenderError, 500 );
        }

        return [];
        
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
