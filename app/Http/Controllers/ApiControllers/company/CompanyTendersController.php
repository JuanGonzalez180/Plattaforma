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
    public function index( $slug )
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();
        
        $company = Company::where('slug', $slug)->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }

        // Traer Licitaciones
        $userTransform = new UserTransformer();

        $company->tenders = Tenders::select('tenders.*')
                            ->where('tenders.company_id', $company->id)
                            ->join( 'projects', 'projects.id', '=', 'tenders.project_id' )
                            ->where('projects.visible', Projects::PROJECTS_VISIBLE)
                            ->orderBy('tenders.updated_at', 'desc')
                            ->get();
        
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
            $TenderError = [ 'blog' => 'Error, no se ha encontrado ninguna licitación' ];
            return $this->errorResponse( $TenderError, 500 );
        }

        $tendersTransformer = new TendersTransformer();

        return $this->showOneData( $tendersTransformer->transformDetail($tender), 200 );
    }


    // public function detail(Request $request, $slug)
    // {
    //     $user       = $this->validateUser();

    //     $name       = $request->name;
    //     $proyect_id = $request->proyect_id;

    //     if($proyect_id) {

    //         $tenders = Tenders::select('tenders.*')
    //             ->where('tenders.project_id','=',$proyect_id)
    //             ->join('companies','companies.id','=','tenders.company_id')
    //             ->where('companies.slug','=',$slug)
    //             ->where(strtolower('tenders.name'),'LIKE','%'.strtolower($name ).'%')
    //             ->orderBy('tenders.updated_at', 'desc')
    //             ->get(); 
    //     } else {

    //         $tenders = Tenders::select('tenders.*')
    //             ->join('companies','companies.id','=','tenders.company_id')
    //             ->where('companies.slug','=',$slug)
    //             ->where(strtolower('tenders.name'),'LIKE','%'.strtolower($name ).'%')
    //             ->orderBy('tenders.updated_at', 'desc')
    //             ->get(); 
    //     }


    //     if( !$tenders ){
    //         $tendersError = [ 'tenders' => 'Error, no se ha encontrado ninguna licitación' ];
    //         return $this->errorResponse( $tendersError, 500 );
    //     }

    //     return $this->showOneTransformNormal($tenders, 200);
    // }

}
