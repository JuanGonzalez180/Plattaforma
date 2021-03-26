<?php

namespace App\Http\Controllers\ApiControllers\projects;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use App\Models\Projects;
use App\Models\MetaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class ProjectsController extends ApiController
{   
    public $routeFile = 'public/';
    public $routeProjects = 'images/projects/';

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function store(Request $request){
        $user = $this->validateUser();

        $rules = [
            'name' => 'required',
            'type' => 'required',
            'status' => 'required',
        ];

        $this->validate( $request, $rules );

        // Iniciar Transacción
        DB::beginTransaction();

        // Datos
        $projectFields['name'] = $request['name'];
        $projectFields['type_projects_id'] = $request['type'];
        $projectFields['user_id'] = $user->id;
        $projectFields['company_id'] = ($user->company && count($user->company)) ? $user->company[0]->id : 0;
        $projectFields['description'] = $request['description'];
        $projectFields['date_start'] = $request['dateStarting'];
        $projectFields['date_end'] = $request['dateEnding'];
        $projectFields['status'] = $request['status'];

        try{
            // Crear Project
            $project = Projects::create( $projectFields );
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorProject = true;
            DB::rollBack();
            $projectError = [ 'project' => 'Error, no se ha podido crear el proyecto' ];
            return $this->errorResponse( $projectError, 500 );
        }

        if( $project ){
            if( $request->image ){
                $png_url = "project-".time().".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",")+1);
                $data = base64_decode($img);
                
                $routeFile = $this->routeProjects.$project->id.'/'.$png_url;
                Storage::disk('local')->put( $this->routeFile . $routeFile, $data);
                $project->image()->create(['url' => $routeFile]);
            }

            if( $request->metadata ){
                foreach ($request->metadata as $key => $metadata) {
                    if( $metadata['name'] ){
                        $project->metadata()->create([ 'name' => $metadata['name'], 'value' => $metadata['value'] ]);
                    }
                }
            }

            if( $request->address || $request->latitud || $request->longitud ){
                $project->address()->create([
                    'address' => $request->address,
                    'latitud' => $request->latitud,
                    'longitud' => $request->longitud
                ]);
            }
        }

        DB::commit();

        return $this->showOne($project,201);
    }
}