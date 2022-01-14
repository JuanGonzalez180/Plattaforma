<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyTeams;

use JWTAuth;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use App\Transformers\TeamsTransformer;
use App\Transformers\UserTransformer;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyTeamsController extends ApiController
{
    //
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index( $slug )
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $company = Company::where('slug', $slug)->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }

        $teams = Team::where('company_id', $company->id)
                ->where('status', Team::TEAM_APPROVED)
                ->orderBy('updated_at', 'desc')
                ->get();
        
        $transformer = Team::TRANSFORMER_TEAM_COMPANY;

        return $this->showAllPaginateSetTransformer($teams, $transformer);
    }

    public function getAdminCompany($slug)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $company = Company::where('slug', $slug)->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }

        $user_admin = $company->user;

        $userTransform = new UserTransformer();

        return $this->showOneData( $userTransform->transform($user_admin), 200 );
    }
}
