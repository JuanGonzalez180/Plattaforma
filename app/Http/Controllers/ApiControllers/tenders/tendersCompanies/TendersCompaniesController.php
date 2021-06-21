<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersCompanies;

use JWTAuth;
use App\Models\Tenders;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendRespondTenderCompany;
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
            $companyError = [ 'company' => 'Error, El usuario no puede listar las compañias participantes' ];
            return $this->errorResponse( $companyError, 500 );
        }

        $companies = TendersCompanies::select('tenders_companies.*', 'images.url')
            ->join('companies', 'companies.id', '=', 'tenders_companies.company_id')
            ->leftJoin('images', function($join)
                            {
                                $join->on('images.imageable_id', '=', 'companies.id');
                                $join->where('images.imageable_type', '=', Company::class);
                            })
            ->where('tenders_companies.tender_id', $tender_id)
            ->get();
            
        return $this->showAllPaginate($companies);
    }

    public function store( Request $request )
    {
        $tender_id = $request->tender_id;
        $companies = $request->companies_id;

        $user = $this->validateUser();
        

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

                $tenderCompanyFields['tender_id']   = $tender_id;
                $tenderCompanyFields['company_id']  = $company["id"];
                $tenderCompanyFields['user_id']     = $user->id;

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
        
    }

    public function update(Request $request, $id)
    {
        $user = $this->validateUser();
        $status = ($request->status == 'True')? TendersCompanies::STATUS_PARTICIPATING : TendersCompanies::STATUS_REJECTED;

        if($user->userType() != 'demanda'){
            $companyError = [ 'tenderCompany' => 'Error, El usuario no puede gestionar la validacion de la compañia hacia la licitación' ];
            return $this->errorResponse( $companyError, 500 );
        }

        $tenderCompany = TendersCompanies::find($id);
        // Iniciar Transacción
        DB::beginTransaction();

        $tenderCompany->status = $status;

        try{
            $tenderCompany->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderCompanyError = [ 'tender' => 'Error, no se ha podido gestionar la solicitud de la compañia'];
            return $this->errorResponse( $tenderCompanyError, 500 );
        }

        DB::commit();

        $email          = $tenderCompany->company->user->email;
        $tender_name    = $tenderCompany->tender->name;
        $company_name   = $tenderCompany->company->name;

        Mail::to($email)->send(new sendRespondTenderCompany(
            $tender_name,
            $company_name,
            $status
        ));

        return $this->showOne($tenderCompany,200);

    }

    public function destroy($id)
    {
        

    }
}
