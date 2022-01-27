<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyCatalogs;

use JWTAuth;
use App\Models\Company;
use App\Models\Catalogs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyCatalogsController extends ApiController
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

        $catalog = Catalogs::where('company_id', $company->id)
                ->where('status', Catalogs::CATALOG_PUBLISH)
                ->orderBy('updated_at', 'desc');

        $catalog = $catalog->get();
        
        return $this->showAllPaginate($catalog);
    }
}
