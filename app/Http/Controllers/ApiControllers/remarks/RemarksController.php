<?php

namespace App\Http\Controllers\ApiControllers\remarks;

use JWTAuth;
use App\Models\Company;
use App\Models\Remarks;
use App\Models\Products;
use App\Models\Projects;
use App\Models\TendersCompanies;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class RemarksController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();
        
        if( $request->type === 'tenders' ){
            $item = TendersCompanies::find($request->id);
        }elseif( $request->type === 'projects' ){
            $item = Projects::find($request->id);
        }elseif( $request->type === 'products' ){
            $item = Products::find($request->id);
        }elseif( $request->type === 'companies' ){
            $item = Company::find($request->id);
        }

        foreach ($item->remarks as $key => $remark) {
            if( $remark->user_id == $user->id ){
                return $this->showOne($remark,200);
            }
        }
        return [];
    }

    public function store(Request $request)
    {
        //
        $user = $this->validateUser();
        if( $request->calification && $request->message ){
            if( $request->type === 'tenders' ){
                $item = TendersCompanies::find($request->id);
                $companyId = ($user->userType() == 'oferta') ? $item->tender->company_id : $item->company_id;
            }elseif( $request->type === 'products' ){
                $item = Products::find($request->id);
                $companyId = $item->company->id;
            }elseif( $request->type === 'projects' ){
                $item = Projects::find($request->id);
                $companyId = $item->company->id;
            }elseif( $request->type === 'companies' ){
                $item = Company::find($request->id);
                $companyId = $item->id;
            }

            $findRemarksBoolean = false; 
            $findRemarks;
            foreach ($item->remarks as $key => $remark) {
                if( $remark->user_id == $user->id ){
                    $findRemarksBoolean = true;
                    $findRemarks = $remark;
                }
            }

            if( $findRemarksBoolean ){
                $findRemarks->delete();
            }

            $item->remarks()->create([
                'user_id' => $user->id, 
                'company_id' => $companyId, 
                'calification'  => $request->calification,
                'message'  => $request->message
            ]);

            return $this->showOne($item,200);
        }else{
            $calificationError = [ 'calification' => 'Error, no se ha podido registrar la calificación' ];
            return $this->errorResponse( $calificationError, 500 );
        }

        return [];
    }

}
