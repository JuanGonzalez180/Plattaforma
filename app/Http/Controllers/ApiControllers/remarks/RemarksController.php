<?php

namespace App\Http\Controllers\ApiControllers\remarks;

use JWTAuth;
use App\Models\Remarks;
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
            $tenderCompany = TendersCompanies::find($request->id);
            foreach ($tenderCompany->remarks as $key => $remark) {
                if( $remark->user_id == $user->id ){
                    return $this->showOne($remark,200);
                }
            }
        }
        return [];
    }

    public function store(Request $request)
    {
        //
        $user = $this->validateUser();
        if( $request->type === 'tenders' ){
            $tenderCompany = TendersCompanies::find($request->id);
            
            if( $request->calification && $request->message ){
                $tenderCompany = TendersCompanies::find($request->id);
                $findRemarksBoolean = false; 
                $findRemarks;
                foreach ($tenderCompany->remarks as $key => $remark) {
                    if( $remark->user_id == $user->id ){
                        $findRemarksBoolean = true;
                        $findRemarks = $remark;
                    }
                }

                if( $findRemarksBoolean ){
                    $findRemarks->delete();
                }

                $tenderCompany->remarks()->create([
                    'user_id' => $user->id, 
                    'company_id' => ($user->userType() == 'oferta') ? $tenderCompany->tender->company_id : $tenderCompany->company_id, 
                    'calification'  => $request->calification,
                    'message'  => $request->message
                ]);

                return $this->showOne($tenderCompany,200);
            }else{
                $calificationError = [ 'calification' => 'Error, no se ha podido registrar la calificación' ];
                return $this->errorResponse( $calificationError, 500 );
            }

        }

        return [];
    }

}
