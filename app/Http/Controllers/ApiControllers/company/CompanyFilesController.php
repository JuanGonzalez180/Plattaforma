<?php

namespace App\Http\Controllers\ApiControllers\company;

use JWTAuth;
use App\Models\User;
use App\Models\Files;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyFilesController extends ApiController
{
    //
    public $routeFile = 'public/';
    public $routeCompanies = 'images/company/';
    public $allowed = ['pdf'];

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function company( User $user ){
        try{
            $company = $user->companyClass();
        } catch (\Throwable $th) {
            return false;
        }

        return $company;
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();
        
        $company = $this->company( $user );
        if( !$company )
            return $this->errorResponse( [ 'company' => ['Ha ocurrido un error al obtener la compañia']], 500 );
        
        return $this->showAll($company->files,200);
    }

    public function store(Request $request){
        $user = $this->validateUser();

        $company = $this->company( $user );
        if( !$company )
            return $this->errorResponse( [ 'company' => ['Ha ocurrido un error al obtener la compañia']], 500 );

        if( $request->hasFile('files') ) {
            $completeFileName = $request->file('files')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extension = strtolower($request->file('files')->getClientOriginalExtension());

            if( in_array( $extension, $this->allowed ) ){
                $fileInServer = 'document' . '-' . rand() . '-' . time() . '.' . $extension;
                $routeFile = $this->routeCompanies.$company->id.'/documents/';
                $request->file('files')->storeAs( $this->routeFile . $routeFile, $fileInServer);
                $company->files()->create([ 'name' => $fileInServer, 'type'=> 'documents', 'url' => $routeFile.$fileInServer]);
            }else{
                return $this->errorResponse( [ 'error' => ['El tipo de archivo no es válido']], 500 );
            }
        }

        return $this->showAll($company->files,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $idProject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $fileId)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $company = $this->company( $user );
        if( !$company )
            return $this->errorResponse( [ 'company' => ['Ha ocurrido un error al obtener la compañia']], 500 );
        
        // Datos
        $fileCompany = Files::where('id', $fileId)->where('filesable_type', Company::class)->first();
        // $company = Company::findOrFail($fileCompany->filesable_id);

        $tmp = explode('.', $fileCompany->name);
        $extension = end($tmp);

        $routeFile = $this->routeCompanies.$company->id.'/documents/';
        $file['name'] = preg_replace("/[^A-Za-z0-9]/", '', $request['name']) . "." . $extension;
        $file['url'] = $routeFile . $file['name'];
        
        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $file['name']) ){
            $userError = [ 'name' => ['Error, el nombre de archivo ya existe'] ];
            return $this->errorResponse( $userError, 500 );
        }

        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $fileCompany->name ) ){
            if(Storage::move(
                $this->routeFile . $routeFile . $fileCompany->name, 
                $this->routeFile . $routeFile . $file['name'])
            ){
                $fileCompany->update( $file );
            }
        }

        return $this->showAll($company->files,200);
    }

    public function destroy(Request $request, int $fileId){
        $user = $this->validateUser();
        
        $company = $this->company( $user );
        if( !$company )
            return $this->errorResponse( [ 'company' => ['Ha ocurrido un error al obtener la compañia']], 500 );

        $fileCompany = Files::where('id', $fileId)->where('filesable_type', Company::class)->first();
        // Eliminar archivo de los datos
        Storage::disk('local')->delete( $this->routeFile . $fileCompany->url );
        // Eliminar archivo de la BD
        $fileCompany->delete();

        // return $this->showOneData( ['success' => 'Se ha eliminado el archivo correctamente', 'code' => 200 ], 200);
        return $this->showAll($company->files,200);
    }
}
