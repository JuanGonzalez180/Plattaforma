<?php

namespace App\Http\Controllers\ApiControllers\projects;

use JWTAuth;
use App\Models\User;
use App\Models\Files;
use App\Models\Company;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class ProjectsFilesController extends ApiController
{
    //
    public $routeFile = 'public/';
    public $routeProjects = 'images/projects/';
    public $allowed = ['jpg','png','jpeg','gif'];

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );
        
        $project = Projects::findOrFail($request->id);
        return $this->showAll($project->files,200);
    }

    public function store(Request $request){
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );

        $project = Projects::findOrFail($request->id);

        if( $request->hasFile('files') ) {
            $completeFileName = $request->file('files')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extension = strtolower($request->file('files')->getClientOriginalExtension());

            if( in_array( $extension, $this->allowed ) ){
                $fileInServer = 'image' . '-' . rand() . '-' . time() . '.' . $extension;
                $routeFile = $this->routeProjects.$project->id.'/images/';
                $request->file('files')->storeAs( $this->routeFile . $routeFile, $fileInServer);
                $project->files()->create([ 'name' => $fileInServer, 'type'=> 'images', 'url' => $routeFile.$fileInServer]);
            }else{
                return $this->errorResponse( [ 'error' => ['El tipo de archivo no es válido']], 500 );
            }
        }

        return $this->showAll($project->files,200);
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
            // 'project' => 'required',
        ];

        $this->validate( $request, $rules );
        
        // Datos
        $fileProject = Files::where('id', $fileId)->where('filesable_type', Projects::class)->first();
        $project = Projects::findOrFail($fileProject->filesable_id);

        $tmp = explode('.', $fileProject->name);
        $extension = end($tmp);

        $routeFile = $this->routeProjects.$project->id.'/images/';
        $file['name'] = preg_replace("/[^A-Za-z0-9]/", '', $request['name']) . "." . $extension;
        $file['url'] = $routeFile . $file['name'];
        
        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $file['name']) ){
            $userError = [ 'name' => ['Error, el nombre de archivo ya existe'] ];
            return $this->errorResponse( $userError, 500 );
        }

        if( Storage::disk('local')->exists( $this->routeFile . $routeFile . $fileProject->name ) ){
            if(Storage::move(
                $this->routeFile . $routeFile . $fileProject->name, 
                $this->routeFile . $routeFile . $file['name'])
            ){
                $fileProject->update( $file );
            }
        }

        return $this->showAll($project->files,200);
    }

    public function destroy(Request $request, int $fileId){
        $user = $this->validateUser();
        
        $rules = [
            'id' => 'required'
        ];
        $this->validate( $request, $rules );

        $project = Projects::findOrFail($request->id);
        
        $fileProject = Files::where('id', $fileId)->where('filesable_type', Projects::class)->first();
        // Eliminar archivo de los datos
        Storage::disk('local')->delete( $this->routeFile . $fileProject->url );
        // Eliminar archivo de la BD
        $fileProject->delete();

        // return $this->showOneData( ['success' => 'Se ha eliminado el archivo correctamente', 'code' => 200 ], 200);
        return $this->showAll($project->files,200);
    }
}
