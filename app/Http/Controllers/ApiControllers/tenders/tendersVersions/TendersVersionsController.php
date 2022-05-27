<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersVersions;

use File;
use JWTAuth;
use Carbon\Carbon;
use App\Models\Files;
use App\Models\Tenders;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersVersionsController extends ApiController
{
    public $routeFile           = 'public/';
    public $routeTenderVersion  = 'images/tenders/';

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

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) //crea nueva adenda de la licitación
    {
        $tender_id  = $request->tender_id;
        $files      = $request['files'];
 
        $lastVersion = TendersVersions::where('tenders_id','=', $tender_id)
            ->orderBy('created_at','DESC')
            ->get()
            ->first();
        
        // if($lastVersion->status == TendersVersions::LICITACION_PUBLISH)
        // {
            $rules = [
                'adenda'    => 'required',
                'price'     => 'required|numeric',
                'project'   => 'required|numeric',
                'date'      => 'required',
                'hour'      => 'required'
            ];


            $project_date_end   = Carbon::parse(Tenders::find($tender_id)->project->date_end);
            $tender_date_end    = Carbon::parse(date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day'])));

            if ($tender_date_end->greaterThan($project_date_end)) {
                $tenderError = ['tender' => 'Error, La fecha de cierre de la licitacion debe ser menor a la fecha de cierre del proyecto'];
                return $this->errorResponse($tenderError, 500);
            }

            // Iniciar Transacción
            DB::beginTransaction();

            $tenderVersionFields['adenda']  = $request['adenda'];
            $tenderVersionFields['price']   = $request['price'];

            if( $request['date'] ){
                $tenderVersionFields['date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
            }
            
            if( $request['hour'] ){
                $tenderVersionFields['hour'] = $this->timeFormat($request['hour']['hour']) . ':' . $this->timeFormat($request['hour']['minute']);
            }

            $tenderVersionFields['tenders_id']  = $tender_id;
            $tenderVersionFields['status']      = TendersVersions::LICITACION_PUBLISH;

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

                        $fileName   = $oldVersion->name;
                        $newFolder  = $this->routeTenderVersion.$id_last.'/documents';

                        $file_old_version = $oldVersion->url;
                        $file_new_version = $newFolder.'/'.$fileName;

                        Storage::copy($this->routeFile.$file_old_version, $this->routeFile.$file_new_version);

                        $tendersVersions->files()->create([ 'name' => $oldVersion->name, 'type'=> $oldVersion->type, 'url' => $file_new_version]);
                    }
                }
            }

            DB::commit();
            return $this->showOne($tendersVersions,201);
        // }else{
        //     $tenderError = [ 'tenderVersion' => 'Error, la ultima versión de la licitacion no esta publicada'];
        //     return $this->errorResponse( $tenderError, 500 );
        // }

        // return [];
        
    }


    public function timeFormat($value)
    {
        return (strlen((string)$value) <= 1) ? '0'.$value : $value; 
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

        // if($lastVersion->status == TendersVersions::LICITACION_CREATED) {
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
            
            // $lastVersion->status      = TendersVersions::LICITACION_CREATED;
            $lastVersion->status      = TendersVersions::LICITACION_PUBLISH;
            
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

        // }else{
        //     $tenderError = [ 'tenderVersion' => 'Error, la ultima versión de la licitacion no esta Borrador'];
        //     return $this->errorResponse( $tenderError, 500 );
        // }

        // return [];
        
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
