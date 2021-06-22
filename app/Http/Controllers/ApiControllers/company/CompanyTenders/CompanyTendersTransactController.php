<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyTenders;

use JWTAuth;
use App\Models\User;
use App\Models\Tenders;
use Illuminate\Http\Request;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendParticipateTenderCompany;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyTendersTransactController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index($slug, Request $request)
    {
        $user           = $this->validateUser();
        $companies_id   = $request->companies_id;

        $id_array = [];

        foreach($companies_id as $company_id){
            $id_array[] = $company_id['id'];
        }

        $tendersCompanies = TendersCompanies::whereIn('id', $id_array)
            ->get();

        $transformer = TendersCompanies::TRANSFORMER_TENDER_COMPANY_SELECTED;

        return $this->showAllPaginateSetTransformer($tendersCompanies, $transformer);
    }
    
    //participar en licitación
    public function store($slug, int $id)
    {
        $user = $this->validateUser();

        $tenderCompany = TendersCompanies::where('tender_id', $id)->where('company_id', $user->companyId());
        $name_company = $user->companyName();


        if($user->userType() != 'oferta'){
            $queryError = [ 'querywall' => 'Error, El no tiene privilegios para participar en una licitación' ];
            return $this->errorResponse( $queryError, 500 );
        }

        if( $tenderCompany->exists() ) {
            $tendersCompaniesError = [ 'tendersCompanies' => 'La compañia ya se encuestra participando en esta licitación' ];
            return $this->errorResponse( $tendersCompaniesError, 500 );
        }

        DB::beginTransaction();

        $tenderCompanyFields['tender_id']   = $id;
        $tenderCompanyFields['company_id']  = $user->companyId();
        $tenderCompanyFields['user_id']     = $user->id;
        $tenderCompanyFields['type']        = TendersCompanies::TYPE_INTERESTED;
        $tenderCompanyFields['status']      = TendersCompanies::STATUS_EARRING;

        try{
            $tenderCompany = TendersCompanies::create( $tenderCompanyFields );
        }catch(\Throwable $th){
            DB::rollBack();
            $tenderCompanyError = [ 'question' => 'Error, no se ha podido gestionar la solicitud' ];
            return $this->errorResponse( $tenderCompanyError, 500 );
        }
        DB::commit();

        $email = Tenders::find($id)->user->email;


        Mail::to($email)->send(new SendParticipateTenderCompany(
            Tenders::find($id)->name,
            $user->companyName()
        ));

        return $this->showOne($tenderCompany,201); 
    } 

}
