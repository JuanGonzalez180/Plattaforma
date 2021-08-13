<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersCompanies;

use JWTAuth;
use App\Models\Tenders;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendWinnerTenderCompany;
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

    public function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();
       
        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
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

        $companyName    = $tenderCompany->company->name;
        $tenderName     = $tenderCompany->tender->name;

        $emails = [];
        $notificationsIds = [];

        $emails[] = $tenderCompany->company->user->email;
        $emails[] = $tenderCompany->user->email;

        $notificationsIds[] = $tenderCompany->company->user->id;
        $notificationsIds[] = $tenderCompany->user->id;
        
        $emails = array_values(array_unique($emails));
        $notificationsIds = array_values(array_unique($notificationsIds));

        foreach($emails as $email){
            Mail::to($email)
                ->send(new SendWinnerTenderCompany($tenderName, $companyName));
        }
        
        $notifications = new Notifications();
        $notifications->registerNotificationQuery( $tenderCompany, Notifications::NOTIFICATION_TENDERCOMPANYSELECTED, $notificationsIds );

        return $this->showOne($tenderCompany,200);
    }

    public function SelectedMoreWinner(Request $request)
    {
        $tenders_companies_ids   = $request->tenders_companies_ids;

        $id_array = [];

        foreach($tenders_companies_ids as $tender_company_id){
            $id_array[] = $tender_company_id['id'];
        }

        $tendersCompanies   = TendersCompanies::whereIn('id',$id_array);
        $tenderVersionLast  = $tendersCompanies->get()->first()->tender->tendersVersionLast();

        DB::beginTransaction();
        $tenderVersionLast->status  = TendersVersions::LICITACION_FINISHED;
        $tendersVersionLastPublish->status  = TendersVersions::LICITACION_FINISHED;

        try{
            $tendersCompanies->update(['winner'=>TendersCompanies::WINNER_TRUE]);
            $tenderVersionLast->save();
            $tendersVersionLastPublish->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderError = [ 'tender' => 'Error, no se ha podido gestionar la solicitud de la licitación'];
            return $this->errorResponse( $tenderError, 500 );
        }

   
        $companiesEmails = [];
        foreach($tendersCompanies->get() as $tenderCompany){

            $companiesEmails[] = array(
                "company_name"  => $tenderCompany->company->name,
                "email"         => $tenderCompany->company->user->email,
            );

            $companiesEmails[] = array(
                "company_name"  => $tenderCompany->company->name,
                "email"         => $tenderCompany->user->email
            );
        }

        $companiesEmails = $this->unique_multidim_array($companiesEmails,'email');

        foreach($companiesEmails as $companyEmail){
            Mail::to($companyEmail['email'])
                ->send(new SendWinnerTenderCompany($tenderVersionLast->tenders->name, $companyEmail['company_name']));
        }


        return $this->showOne($tenderVersionLast,200);
    }

}
