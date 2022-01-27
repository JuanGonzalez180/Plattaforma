<?php

namespace App\Http\Controllers\ApiControllers\blog;

use JWTAuth;
use App\Models\User;
use App\Models\Blog;
use App\Models\Files;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class BlogFilesController extends ApiController
{
    //
    public $routeFile = 'public/';
    public $routeBlogs = 'images/blogs/';
    public $allowed = ['jpg','png','jpeg','gif'];

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }
    
    public function filesType( $blog ){
        $files = Files::where('filesable_id', $blog->id)->where('type', 'images')->where('filesable_type', Blog::class)->get();
        return $files;
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );
        
        $blog = Blog::findOrFail($request->id);
        return $this->showAll($this->filesType( $blog ),200);
    }

    public function store(Request $request){
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );

        $blog = Blog::findOrFail($request->id);

        if( $request->hasFile('files') ) {
            $completeFileName = $request->file('files')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extension = strtolower($request->file('files')->getClientOriginalExtension());

            if( in_array( $extension, $this->allowed ) ){
                $fileInServer = 'image' . '-' . rand() . '-' . time() . '.' . $extension;
                $routeFile = $this->routeBlogs.$blog->id.'/images/';
                $request->file('files')->storeAs( $this->routeFile . $routeFile, $fileInServer);
                $blog->files()->create([ 'name' => $fileInServer, 'type'=> 'images', 'url' => $routeFile.$fileInServer]);
            }else{
                return $this->errorResponse( [ 'error' => ['El tipo de archivo no es válido']], 500 );
            }
        }

        return $this->showAll($this->filesType( $blog ),200);
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

        $rules = [
            'id' => 'required',
            'name' => 'required',
            // 'product' => 'required',
        ];

        $this->validate( $request, $rules );
        
        // Datos
        $fileProduct = Files::where('id', $fileId)->where('filesable_type', Blog::class)->first();
        $blog = Blog::findOrFail($fileProduct->filesable_id);

        $tmp = explode('.', $fileProduct->name);
        $extension = end($tmp);

        $routeFile = $this->routeBlogs.$blog->id.'/images/';
        $file['name'] = preg_replace("/[^A-Za-z0-9]/", '', $request['name']) . "." . $extension;
        $file['url'] = $routeFile . $file['name'];
        
        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $file['name']) ){
            $userError = [ 'name' => ['Error, el nombre de archivo ya existe'] ];
            return $this->errorResponse( $userError, 500 );
        }

        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $fileProduct->name ) ){
            if(Storage::move(
                $this->routeFile . $routeFile . $fileProduct->name, 
                $this->routeFile . $routeFile . $file['name'])
            ){
                $fileProduct->update( $file );
            }
        }

        return $this->showAll($this->filesType( $blog ),200);
    }

    public function destroy(Request $request, int $fileId){
        $user = $this->validateUser();
        
        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );

        $blog = Blog::findOrFail($request->id);
        
        $fileProduct = Files::where('id', $fileId)->where('filesable_type', Blog::class)->first();
        // Eliminar archivo de los datos
        Storage::disk('local')->delete( $this->routeFile . $fileProduct->url );
        // Eliminar archivo de la BD
        $fileProduct->delete();

        // return $this->showOneData( ['success' => 'Se ha eliminado el archivo correctamente', 'code' => 200 ], 200);
        return $this->showAll($this->filesType( $blog ),200);
    }
}
