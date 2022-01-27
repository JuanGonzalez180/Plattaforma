<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyProjects;

use JWTAuth;
use App\Models\Company;
use App\Models\Projects;
use App\Http\Controllers\ApiControllers\ApiController;
use Illuminate\Http\Request;
use App\Transformers\ProjectsTransformer;

class CompanyProjectsController extends ApiController
{
    //
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
    public function index( $slug )
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();
        
        $company = Company::where('slug', $slug)->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }

        // Traer Proyectos de la compañía
        $company->projects = $company->projects
                        ->where('visible', Projects::PROJECTS_VISIBLE)
                        ->sortBy([ ['updated_at', 'desc'] ]);
        
        return $this->showAllPaginate($company->projects);
    }

    public function show( $slug, $id )
    {
        $user = $this->validateUser();
        
        $project = Projects::where('id', $id)
                            ->where('visible',Projects::PROJECTS_VISIBLE)
                            ->first();
        
        if( !$id || !$project ){
            $projectsError = [ 'project' => 'Error, no se ha encontrado ningun proyecto' ];
            return $this->errorResponse( $projectsError, 500 );
        }
        
        $projectTransform = new ProjectsTransformer();

        return $this->showOneData( $projectTransform->transformDetail($project), 200 );
    }

}
