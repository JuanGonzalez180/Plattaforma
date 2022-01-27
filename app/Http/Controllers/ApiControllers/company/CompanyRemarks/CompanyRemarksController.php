<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyRemarks;

use JWTAuth;
use App\Models\Company;
use App\Models\Remarks;
use Illuminate\Http\Request;
// use App\Transformers\RemarksTransformer;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyRemarksController extends ApiController
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
       
        // Calificaciones.
        $remarks = Remarks::select('remarks.*')
                ->where('remarks.company_id', $company->id )
                ->orderBy('id', 'desc');

        // foreach ( $remarks as $key => $remark) {
        //     $user = $userTransform->transform($remark->user);
        //     unset( $remark->user );
        //     $remark->user = $user;
        // }

        $remarks = $remarks->get();
        
        return $this->showAllPaginate($remarks);
    }
    
}
