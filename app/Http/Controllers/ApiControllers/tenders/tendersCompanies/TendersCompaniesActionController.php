<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersCompanies;

use JWTAuth;
use App\Models\Tenders;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersCompaniesActionController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function SelectedWinner(Request $request)
    {
        $id = $request->id;

        $tenderCompany      = TendersCompanies::find($id);
        $tenderVersionLast  = $tenderCompany->tender->tendersVersionLast();

        DB::beginTransaction();

        $tenderCompany->winner      = TendersCompanies::WINNER_TRUE;
        $tenderVersionLast->status  = TendersVersions::LICITACION_FINISHED;

        try{
            $tenderCompany->save();
            $tenderVersionLast->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderError = [ 'tender' => 'Error, no se ha podido gestionar la solicitud de la licitación'];
            return $this->errorResponse( $tenderError, 500 );
        }

        return $this->showOne($tenderCompany,200);
    }

    public function SelectedMoreWinner(Request $request)
    {
        $tenders_companies_ids   = $request->tenders_companies_ids;

        $id_array = [];

        foreach($tenders_companies_ids as $tender_company_id){
            $id_array[] = $tender_company_id['id'];
        }

        $TendersCompanies   = TendersCompanies::whereIn('id',$id_array);
        $tenderVersionLast  = $TendersCompanies->get()->first()->tender->tendersVersionLast();

        DB::beginTransaction();
        $tenderVersionLast->status  = TendersVersions::LICITACION_FINISHED;
        $tendersVersionLastPublish->status  = TendersVersions::LICITACION_FINISHED;

        try{
            $TendersCompanies->update(['winner'=>TendersCompanies::WINNER_TRUE]);
            $tenderVersionLast->save();
            $tendersVersionLastPublish->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderError = [ 'tender' => 'Error, no se ha podido gestionar la solicitud de la licitación'];
            return $this->errorResponse( $tenderError, 500 );
        }

        return $this->showOne($tenderVersionLast,200);
    }
}
