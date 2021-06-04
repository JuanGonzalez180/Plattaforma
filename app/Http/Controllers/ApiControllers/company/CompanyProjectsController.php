<?php

namespace App\Http\Controllers\ApiControllers\company;

use JWTAuth;
use App\Models\Company;
use App\Models\Projects;
use App\Http\Controllers\ApiControllers\ApiController;
use Illuminate\Http\Request;

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

    public function detail(Request $request, $slug)
    {
        $user = $this->validateUser();

        $name = $request->name;

        $projects = Projects::select('projects.*')
            ->where('projects.visible','=',Projects::PROJECTS_VISIBLE)
            ->join('companies','companies.id','=','projects.company_id')
            ->where('companies.slug','=',$slug)
            ->where(strtolower('projects.name'),'LIKE','%'.strtolower($name ).'%')
            ->orderBy('projects.updated_at', 'desc')
            ->get(); 

        if( !$projects ){
            $projectsError = [ 'projects' => 'Error, no se ha encontrado ningun proyecto' ];
            return $this->errorResponse( $projectsError, 500 );
        }

        return $this->showOneTransformNormal($projects, 200);
    }

}
