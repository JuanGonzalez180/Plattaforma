<?php

namespace App\Http\Controllers\ApiControllers\tenderscompanies;

use JWTAuth;
use Illuminate\Http\Request;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersCompaniesController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index()
    {
        //
    }

    public function store( Request $request )
    {
        $tender_id = $request->tender_id;
        $comanies = $request->comanies_id;

        $tendersCompanies = [];

        foreach($comanies as $company){
            // Iniciar Transacción
            DB::beginTransaction();

            $tenderCompanyFields['tender_id']  = $tender_id;
            $tenderCompanyFields['company_id'] = $company["id"];

            try{
                $tendersCompanies[] = TendersCompanies::create( $tenderCompanyFields );
            } catch (\Throwable $th) {
                $errorTenderCompany = true;
                DB::rollBack();
                $tenderCompanyError = [ 'tenderVersion' => 'Error, no se ha podido crear la compania del tenders'];
                return $this->errorResponse( $tenderCompanyError, 500 );
            }

            DB::commit();            
        }

        // return $this->showOne($tendersCompanies,201);
        return $tendersCompanies;
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id)
    {

    }
}
