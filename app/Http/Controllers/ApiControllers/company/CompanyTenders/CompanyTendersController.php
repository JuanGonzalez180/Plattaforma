<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyTenders;

use JWTAuth;
use App\Models\Company;
use App\Models\Projects;
use App\Models\Tenders;
use App\Models\TendersCompanies;
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
        // Compañía del usuario que está logueado
        $userCompanyId = $user->companyId();
        $project_id = $request->project_id;
        
        $company = Company::where('slug', $slug )->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }

        // Traer Licitaciones
        $userTransform = new UserTransformer();

        $tenders = Tenders::select('tenders.*', 'comp.status AS company_status')
                    ->where('tenders.company_id', $company->id )
                    ->join( 'projects', 'projects.id', '=', 'tenders.project_id' );

        if($project_id > 0) { $tenders = $tenders->where('projects.id', $project_id); };

        $tenders = $tenders->where('projects.visible', Projects::PROJECTS_VISIBLE)
                    ->leftjoin('tenders_companies AS comp', function($join) use($userCompanyId){
                        $join->on('tenders.id', '=', 'comp.tender_id');
                        $join->where('comp.company_id', '=', $userCompanyId);
                    })
                    ->orderBy('tenders.updated_at', 'desc')
                    ->get();

        $company->tenders = $tenders;
        
        foreach ( $company->tenders as $key => $tender) {
            $user = $userTransform->transform($tender->user);
            unset( $tender->user );
            $tender->user = $user;

            $version = $tender->tendersVersionLastPublish();
            if( $version ){
                $tender->tags = $version->tags;
            }
            $tender->project;
        }
        
        return $this->showAllPaginate($company->tenders);
    }

    public function show( $slug, $id ) {

        $user = $this->validateUser();
        // Compañía del usuario que está logueado
        $userCompanyId = $user->companyId();

        $tender = Tenders::where('id', $id)->first();
        
        // Tenders Company
        $tender->company_status = '';
        $tenderCompany = TendersCompanies::where('tender_id', $id)
                        ->where('company_id', $userCompanyId)
                        ->first();
        if( $tenderCompany && $tenderCompany->status ){
            $tender->company_status = $tenderCompany->status;
        }

        if( !$id || !$tender ){
            $TenderError = [ 'company' => 'Error, no se ha encontrado ninguna licitación' ];
            return $this->errorResponse( $TenderError, 500 );
        }
        
        // Traer Licitaciones
        $userTransform = new UserTransformer();
        $user = $userTransform->transform($tender->user);
        unset( $tender->user );
        $tender->user = $user;

        $version = $tender->tendersVersionLastPublish();
        if( $version ){
            $tender->tags = $version->tags;
        }

        $tendersTransformer = new TendersTransformer();
        
        if ( $tender->company_status == TendersCompanies::STATUS_PARTICIPATING || $tender->company_status == TendersCompanies::STATUS_PROCESS ){

            foreach( $tender->tendersVersion as $key => $version ){
                $version->files;
            }

            return $this->showOneData( $tendersTransformer->transformDetail($tender), 200 );
        }
        
        // Solamente estos datos
        $tender->tendersVersionLastPublish = $tender->tendersVersionLastPublish();
        $tender->categories = $tender->categories;

        return $this->showOne( $tender, 200 );
    }

}
