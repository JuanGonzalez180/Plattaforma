<?php

namespace App\Http\Controllers\ApiControllers\tenderscompanies;

use JWTAuth;
use Illuminate\Http\Request;
use App\Models\TendersCompanies;
use App\Models\TendersVersions;
use App\Models\Tenders;
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
        $companies = $request->companies_id;

        $tendersCompanies = [];

        $tender = Tenders::findOrFail($tender_id);

        if( count($companies) + count($tender->tenderCompanies) < 3 ){
            $tenderCompanyError = [ 'tenderVersion' => 'Error, Se debe seleccionar mínimo 3 compañías'];
            return $this->errorResponse( $tenderCompanyError, 500 );
        }

        if( $companies ){
            foreach($companies as $company){
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

                $tenderVersion = $tender->tendersVersionLast();
                $tenderVersion->status = TendersVersions::LICITACION_PUBLISH;
                $tenderVersion->save();

                DB::commit();
            }
        }

        // return $this->showOne($tendersCompanies,201);
        return $this->showOne($tender,201);
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
