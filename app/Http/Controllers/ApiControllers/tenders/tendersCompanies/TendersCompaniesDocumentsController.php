<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersCompanies;

use JWTAuth;
use App\Models\User;
use App\Models\Files;
use Illuminate\Http\Request;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersCompaniesDocumentsController extends ApiController
{
    public $routeFile           = 'public/';
    public $routeTenderCompany  = 'images/tendercompany/';
    public $allowed             = ['pdf'];

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function filesType( $tendercompany ){
        $files = Files::where('filesable_id', $tendercompany->id)
            ->where('type', 'documents')
            ->where('filesable_type', TendersCompanies::class)
            ->get();

        return $files;
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );
        
        $tendersCompanies = TendersCompanies::findOrFail($request->id);
        return $this->showAll($this->filesType( $tendersCompanies ),200);
    }

    public function store(Request $request){
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );

        $tendersCompanies = TendersCompanies::findOrFail($request->id);

        if( $request->hasFile('files') ) {
            $completeFileName = $request->file('files')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extension = strtolower($request->file('files')->getClientOriginalExtension());

            if( in_array( $extension, $this->allowed ) ){
                $fileInServer = 'document' . '-' . rand() . '-' . time() . '.' . $extension;
                $routeFile = $this->routeTenderCompany.$tendersCompanies->id.'/documents/';
                $request->file('files')->storeAs( $this->routeFile . $routeFile, $fileInServer);
                $tendersCompanies->files()->create([ 'name' => $fileInServer, 'type'=> 'documents', 'url' => $routeFile.$fileInServer]);
            }else{
                return $this->errorResponse( [ 'error' => ['El tipo de archivo no es válido']], 500 );
            }
        }

        return $this->showAll($this->filesType( $tendersCompanies ),200);
    }

    public function update(Request $request, int $fileId)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $rules = [
            'id' => 'required',
            'name' => 'required',
            // 'product' => 'required',
        ];

        $this->validate( $request, $rules );
        
        // Datos
        $fileTendersCompanies = Files::where('id', $fileId)
            ->where('filesable_type', TendersCompanies::class)
            ->first();

        $tendersCompanies = TendersCompanies::findOrFail($fileTendersCompanies->filesable_id);

        $tmp = explode('.', $fileTendersCompanies->name);
        $extension = end($tmp);

        $routeFile = $this->tendersCompanies.$tendersCompanies->id.'/documents/';
        $file['name'] = preg_replace("/[^A-Za-z0-9]/", '', $request['name']) . "." . $extension;
        $file['url'] = $routeFile . $file['name'];
        
        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $file['name']) ){
            $userError = [ 'name' => ['Error, el nombre de archivo ya existe'] ];
            return $this->errorResponse( $userError, 500 );
        }

        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $fileTendersCompanies->name ) ){
            if(Storage::move(
                $this->routeFile . $routeFile . $fileTendersCompanies->name, 
                $this->routeFile . $routeFile . $file['name'])
            ){
                $fileTendersCompanies->update( $file );
            }
        }

        return $this->showAll($this->filesType( $tendersCompanies ),200);
    }

    public function destroy(Request $request, int $fileId){
        $user = $this->validateUser();
        
        $rules = [
            'id' => 'required'
        ];
        
        $this->validate( $request, $rules );

        $tendersCompanies = TendersCompanies::findOrFail($request->id);
        
        $filetendersCompanies = Files::where('id', $fileId)->where('filesable_type', TendersCompanies::class)->first();
        // Eliminar archivo de los datos
        Storage::disk('local')->delete( $this->routeFile . $filetendersCompanies->url );
        // Eliminar archivo de la BD
        $filetendersCompanies->delete();

        // return $this->showOneData( ['success' => 'Se ha eliminado el archivo correctamente', 'code' => 200 ], 200);
        return $this->showAll($this->filesType( $tendersCompanies ),200);
    }
}
