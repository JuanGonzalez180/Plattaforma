<?php

namespace App\Http\Controllers\ApiControllers\catalogs;

use JWTAuth;
use App\Models\User;
use App\Models\Files;
use App\Models\Company;
use App\Models\Catalogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class CatalogsDocumentsControllers extends ApiController
{
    public $routeFile = 'public/';
    public $routeCatalog = 'images/catalogs/';
    public $allowed = ['pdf'];

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function filesType( $catalog ){
        $files = Files::where('filesable_id', $catalog->id)
            ->where('type', 'documents')
            ->where('filesable_type', Catalogs::class)
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
        
        $catalog = Catalogs::findOrFail($request->id);
        return $this->showAll($this->filesType( $catalog ),200);
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
    public function store(Request $request){
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );

        $catalog = Catalogs::findOrFail($request->id);

        if( $request->hasFile('files') ) {
            $completeFileName = $request->file('files')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extension = strtolower($request->file('files')->getClientOriginalExtension());

            if( in_array( $extension, $this->allowed ) ){
                $fileInServer = 'document' . '-' . rand() . '-' . time() . '.' . $extension;
                $routeFile = $this->routeCatalog.$catalog->id.'/documents/';
                $request->file('files')->storeAs( $this->routeFile . $routeFile, $fileInServer);
                $catalog->files()->create([ 'name' => $fileInServer, 'type'=> 'documents', 'url' => $routeFile.$fileInServer]);
            }else{
                return $this->errorResponse( [ 'error' => ['El tipo de archivo no es válido']], 500 );
            }
        }

        return $this->showAll($this->filesType( $catalog ),200);
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
        $fileCatalog = Files::where('id', $fileId)
            ->where('filesable_type', Catalogs::class)
            ->first();

        $catalog = Catalogs::findOrFail($fileCatalog->filesable_id);

        $tmp = explode('.', $fileCatalog->name);
        $extension = end($tmp);

        $routeFile = $this->routeCatalog.$catalog->id.'/documents/';
        $file['name'] = preg_replace("/[^A-Za-z0-9]/", '', $request['name']) . "." . $extension;
        $file['url'] = $routeFile . $file['name'];
        
        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $file['name']) ){
            $userError = [ 'name' => ['Error, el nombre de archivo ya existe'] ];
            return $this->errorResponse( $userError, 500 );
        }

        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $fileCatalog->name ) ){
            if(Storage::move(
                $this->routeFile . $routeFile . $fileCatalog->name, 
                $this->routeFile . $routeFile . $file['name'])
            ){
                $fileCatalog->update( $file );
            }
        }

        return $this->showAll($this->filesType( $catalog ),200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $fileId){
        $user = $this->validateUser();
        
        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );

        $catalog = Catalogs::findOrFail($request->id);
        
        $fileCatalog = Files::where('id', $fileId)->where('filesable_type', Catalogs::class)->first();
        // Eliminar archivo de los datos
        Storage::disk('local')->delete( $this->routeFile . $fileCatalog->url );
        // Eliminar archivo de la BD
        $fileCatalog->delete();

        // return $this->showOneData( ['success' => 'Se ha eliminado el archivo correctamente', 'code' => 200 ], 200);
        return $this->showAll($this->filesType( $catalog ),200);
    }
}
