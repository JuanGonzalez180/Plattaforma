<?php

namespace App\Http\Controllers\ApiControllers\company;

use JWTAuth;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Projects;
use App\Transformers\UserTransformer;
use App\Transformers\TendersTransformer;
use App\Http\Controllers\ApiControllers\ApiController;
use Illuminate\Http\Request;

class CompanyTendersController extends ApiController
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
    public function index( $slug, Request $request )
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();
        $project_id = $request->project_id;
        
        $company = Company::where('slug', $slug )->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }

        // Traer Licitaciones
        $userTransform = new UserTransformer();

        $tenders = Tenders::select('tenders.*')
                    ->where('tenders.company_id', $company->id )
                    ->join( 'projects', 'projects.id', '=', 'tenders.project_id' );

        if($project_id > 0) { $tenders = $tenders->where('projects.id', $project_id); };

        $tenders = $tenders->where('projects.visible', Projects::PROJECTS_VISIBLE)
                    ->orderBy('tenders.updated_at', 'desc')
                    ->get();

        $company->tenders = $tenders;
        
        foreach ( $company->tenders as $key => $tender) {
            $user = $userTransform->transform($tender->user);
            unset( $tender->user );
            $tender->user = $user;

            $version = $tender->tendersVersionLast();
            if( $version ){
                $tender->tags = $version->tags;
            }
            $tender->project;
        }
        
        return $this->showAllPaginate($company->tenders);
    }

    public function show( $slug, $id ) {

        $user = $this->validateUser();

        $tender = Tenders::where('id', $id)->first();

        if( !$id || !$tender ){
            $TenderError = [ 'company' => 'Error, no se ha encontrado ninguna licitación' ];
            return $this->errorResponse( $TenderError, 500 );
        }
        
        // Traer Licitaciones
        $userTransform = new UserTransformer();
        $user = $userTransform->transform($tender->user);
        unset( $tender->user );
        $tender->user = $user;

        $version = $tender->tendersVersionLast();
        if( $version ){
            $tender->tags = $version->tags;
        }

        $tendersTransformer = new TendersTransformer();

        return $this->showOneData( $tendersTransformer->transformDetail($tender), 200 );
    }

}
