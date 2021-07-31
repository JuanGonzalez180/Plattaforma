<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyPortfolios;

use JWTAuth;
use App\Models\Company;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyPortfoliosController extends ApiController
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

        $portfolios = Portfolio::where('company_id', $company->id)
                ->where('status', Portfolio::PORTFOLIO_PUBLISH)
                ->orderBy('updated_at', 'desc');
        
        foreach ( $portfolios as $key => $portfolio) {
            $portfolio->image;
            $portfolio->files;
        }

        $portfolios = $portfolios->get();
        
        return $this->showAllPaginate($portfolios);
    }
    
}
