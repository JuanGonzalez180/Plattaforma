<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyTeams;

use JWTAuth;
use App\Models\Company;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Transformers\TeamsTransformer;
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
                ->skip(0)->take(8)
                ->orderBy('updated_at', 'desc')
                ->get();
        
        $transformer = Team::TRANSFORMER_TEAM_COMPANY;

        return $this->showAllPaginateSetTransformer($teams, $transformer);
    }

    /*public function show( $slug, $id ) {

        $user = $this->validateUser();

        $product = Products::where('id', $id)
                        ->where('status',Products::PRODUCT_PUBLISH)
                        ->first();

        if( !$id || !$product ){
            $productError = [ 'project' => 'Error, no se ha encontrado ningun producto' ];
            return $this->errorResponse( $productError, 500 );
        }

        $productTransform = new ProductsTransformer();

        return $this->showOneData( $productTransform->transformDetail($product), 200 );
    }*/
    
}
