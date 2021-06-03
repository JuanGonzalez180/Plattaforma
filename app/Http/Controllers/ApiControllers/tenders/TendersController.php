<?php

namespace App\Http\Controllers\ApiControllers\tenders;

use JWTAuth;
use App\Models\User;
use App\Models\Images;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Projects;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersController extends ApiController
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
    public function index( Request $request )
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();
        
        $rules = [
            'project' => 'required|numeric',
        ];
        
        $this->validate( $request, $rules );

        //IS ADMIN
        $companyID = $user->companyId();

        if( $companyID && $user->userType() == 'demanda' ){
            $tenders = Tenders::where('company_id', $companyID)->where('project_id', $request->project)->get();

            foreach( $tenders as $key => $tender ){
                $tender->user;
                // $tender->tendersVersionLast = $tender->tendersVersionLast();
            }

            return $this->showAllPaginate($tenders);
        }
        return [];
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
        $user = $this->validateUser();

        $rules = [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'project' => 'required|numeric',
            'date' => 'required',
            'hour' => 'required'
        ];

        $this->validate( $request, $rules );
        
        // Iniciar Transacción
        DB::beginTransaction();

        // Datos
        $tendersFields['name'] = $request['name'];
        $tendersFields['description'] = $request['description'];
        $tendersFields['user_id'] = $request['user'] ?? $user->id;
        $tendersFields['company_id'] = $user->companyId();
        $tendersFields['project_id'] = $request['project'];
        
        $tendersVersionFields['adenda'] = $request['adenda'];
        $tendersVersionFields['price'] = $request['price'];
        if( $request['date'] ){
            $tendersVersionFields['date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
        }
        if( $request['hour'] ){
            $tendersVersionFields['hour'] = $request['hour']['hour'] . ':' . $request['hour']['minute'];
        }

        try{
            $tender = Tenders::create( $tendersFields );
            
            $tendersVersionFields['tenders_id'] = $tender->id;
            $tendersVersions = TendersVersions::create( $tendersVersionFields );

            // Crear Tenders
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorTender = true;
            DB::rollBack();
            $tenderError = [ 'tender' => 'Error, no se ha podido crear el tenders'];
            return $this->errorResponse( $tenderError, 500 );
        }

        if( $tender ){
            if( $request->categories ){
                foreach ($request->categories as $key => $categoryId) {
                    $tender->tenderCategories()->attach($categoryId);
                }
            }

            foreach ($request->tags as $key => $tag) {
                $tendersVersions->tags()->create(['name' => $tag['displayValue']]);
            }

            $tender->tendersVersions = $tendersVersions;
        }
        DB::commit();

        return $this->showOne($tender,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function companies($id){
        return TendersCompanies::select('companies.id', 'companies.name', 'images.url')
            ->join('companies', 'companies.id', '=', 'tenders_companies.company_id')
            ->leftJoin('images', function($join)
                         {
                             $join->on('images.imageable_id', '=', 'companies.id');
                             $join->where('images.imageable_type', '=', Company::class);
                         })
            ->where('tenders_companies.tender_id', $id)
            ->get();
    }

    public function show($id)
    {
        //
        $user = $this->validateUser();

        $tender = Tenders::findOrFail($id);
        $tender->categories;
        $tender->tendersVersion;
        $tender->tenderCompanies;
        
        $tender->companies = $this->companies($tender->id);

        if( $tender->tendersVersion ){
            foreach ($tender->tendersVersion as $key => $tenderVersion) {
                $tenderVersion->tags;
                $tenderVersion->files;
            }
        }

        return $this->showOne($tender,201);
    }

    public function edit($id)
    {
        //
        $user = $this->validateUser();

        $tender = Tenders::findOrFail($id);
        $tender->user;
        $tender->categories;
        $tender->version_last = $tender->tendersVersionLast();
        $tender->version_last->tags;
        $tender->version_last->files;

        return $this->showOne($tender,201);
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

        $count = TendersVersions::where('tenders_id',$id)
            ->where('status','=',TendersVersions::LICITACION_CREATED)
            ->get()
            ->count();

        if($count == 1){
            $user = $this->validateUser();

            $rules = [
                //tender
                'name' => 'required',
                'description' => 'required',
                'project' => 'required|numeric',
                //tender_version
                'price' => 'required|numeric',
                'project' => 'required|numeric',
                'date' => 'required',
                'hour' => 'required'
            ];

            $this->validate( $request, $rules );


            DB::beginTransaction();

            $tender = Tenders::findOrFail($id);

            //tender
            $tenderFields['name']           = $request['name'];
            $tenderFields['description']    = $request['description'];
            $tenderFields['project_id']     = $request['project'];
            $tenderFields['company_id']     = $user->companyId();
            $tenderFields['user_id']        = $request['user'] ?? $user->id;
            
            //tender_version
            $tenderVersionFields['adenda']  = $request['adenda'];
            $tenderVersionFields['price']   = $request['price'];

            if( $request['date'] ){
                $tenderVersionFields['date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
            }
            if( $request['hour'] ){
                $tenderVersionFields['hour'] = $request['hour']['hour'] . ':' . $request['hour']['minute'];
            }

            try{
                $tender->update( $tenderFields );

                // Categorías
                // Eliminar los anteriores
                foreach( $tender->tenderCategories as $key => $category ){
                    $tender->tenderCategories()->detach($category->id);
                }

                if( $request->categories ){
                    foreach ($request->categories as $key => $categoryId) {
                        $tender->tenderCategories()->attach($categoryId);
                    }
                }

                $tenderVersion = TendersVersions::where('tenders_id',$id)
                    ->where('status','=',TendersVersions::LICITACION_CREATED)
                    ->get()
                    ->first();
                
                $tenderVersion->update( $tenderVersionFields );

                // Tags
                // Eliminar los anteriores
                foreach( $tenderVersion->tags as $key => $tag ){
                    $tag->delete();
                }

                foreach ($request->tags as $key => $tag) {
                    $tenderVersion->tags()->create(['name' => $tag['displayValue']]);
                }

                $tender->tendersVersions = $tenderVersion;
            } catch (\Throwable $th) {
                // Si existe algún error al momento de editar el tender
                $errorTender = true;
                DB::rollBack();
                $tenderError = [ 'tender' => 'Error, no se ha podido editar la licitación'];
                return $this->errorResponse( $tenderError, 500 );
            }

            DB::commit();

            return $this->showOne($tender,200);
        }else{
            $tenderError = [ 'tender' => 'Error, no se ha podido editar la licitación, tiene más versiones.'];
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
