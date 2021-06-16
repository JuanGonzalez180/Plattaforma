<?php

namespace App\Http\Controllers\ApiControllers\tenderscompanies;

use JWTAuth;
use App\Models\Tenders;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendInvitationTenderCompany;
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

    public function index( Request $request )
    {
        $user = $this->validateUser();
        $tender_id = $request->tender_id;

        if($user->userType() != 'demanda'){
            $companyError = [ 'querywall' => 'Error, El usuario no puede listar las compañias participantes' ];
            return $this->errorResponse( $companyError, 500 );
        }

        $Companies = TendersCompanies::where('tender_id', $tender_id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return $this->showAllPaginate($Companies);
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

        // Iniciar Transacción
        DB::beginTransaction();

        if( $companies ){

            foreach($companies as $company){

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

            }

        }

        $tender                 = Tenders::findOrFail($tender_id);
        $tenderVersion          = $tender->tendersVersionLast();
        $tenderVersion->status  = TendersVersions::LICITACION_PUBLISH;
        $tenderVersion->save();

        //envia invitacion a correos
        foreach($companies as $company){

            $companyInfo = Company::findOrFail($company["id"]);

            Mail::to($companyInfo->user->email)->send(new SendInvitationTenderCompany(
                $tender->name, 
                $tenderVersion->adenda, 
                $companyInfo->name
            ));
        }

        DB::commit();

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
