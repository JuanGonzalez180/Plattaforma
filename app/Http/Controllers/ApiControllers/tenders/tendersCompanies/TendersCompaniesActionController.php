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
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public function desertTender(Request $request)
    {
        $id = $request->id;

        $tender                     = Tenders::find($id);
        $tenderVersionLast          = $tender->tendersVersionLast();

        DB::beginTransaction();

        $tenderVersionLast->status  = TendersVersions::LICITACION_DESERTED;

        try {
            $tenderVersionLast->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderError = ['tender' => 'Error, no se ha podido gestionar la solicitud de la licitación'];
            return $this->errorResponse($tenderError, 500);
        }

        return $this->showOne($tenderVersionLast, 200);
    }

    public function SelectedWinner(Request $request)
    {
        $id = $request->id;

        $tenderCompany      = TendersCompanies::find($id);
        $tenderVersionLast  = $tenderCompany->tender->tendersVersionLast();

        DB::beginTransaction();

        $tenderCompany->winner      = TendersCompanies::WINNER_TRUE;
        $tenderVersionLast->status  = TendersVersions::LICITACION_FINISHED;

        try {
            $tenderCompany->save();
            $tenderVersionLast->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderError = ['tender' => 'Error, no se ha podido gestionar la solicitud de la licitación'];
            return $this->errorResponse($tenderError, 500);
        }
        //envia los correos a las empresas que participaron en la licitación
        $this->sendEmailTenderCompanies($tenderCompany);
        //envia las notificaciones a las compañias licitantes. incluyendo a la compañia ganadora y a las demas que participarón
        $this->sendNotificationTenderCompanies($tenderCompany);

        return $this->showOne($tenderCompany, 200);
    }

    public function sendEmailTenderCompanies($tenderCompany)
    {
        $companyName    = $tenderCompany->company->name;
        $tenderName     = $tenderCompany->tender->name;
        $emails = [];

        //se hace un array del correo del responsable de la licitación y el admin de la compañia.
        $emails = $this->getTendersCompaniesEmail($tenderCompany->tender);

        foreach ($emails as $email) {
            Mail::to(trim($email))
                ->send(new SendWinnerTenderCompany($tenderName, $companyName));
        }
    }

    public function sendNotificationTenderCompanies($tenderCompany)
    {
        $notificationsIds = $this->getTendersCompaniesUsers($tenderCompany->tender);

        $notifications = new Notifications();
        $notifications->registerNotificationQuery($tenderCompany, Notifications::NOTIFICATION_TENDERCOMPANYSELECTED, $notificationsIds);
    }

    public function getTendersCompaniesEmail($tender)
    {
        $emails = [];

        $tenderCompanies = $tender->tenderCompaniesActive();

        foreach ($tenderCompanies as $tenderCompany)
        {
            $emails[] = $tenderCompany->company->user->email;
            $emails[] = $tenderCompany->userCompany->email;
        }

        return array_unique($emails);

        // return array_values(array_unique(TendersCompanies::where('tenders_companies.tender_id', $tender->id)
        //     ->join('companies', 'companies.id', '=', 'tenders_companies.company_id')
        //     ->where('tenders_companies.status','=',TendersCompanies::STATUS_PARTICIPATING)
        //     ->join('users', 'users.id', '=', 'companies.user_id')
        //     ->pluck('users.email')
        //     ->all()));
    }

    public function getTendersCompaniesUsers($tender)
    {
        $userIds = [];

        $tenderCompanies = $tender->tenderCompaniesActive();

        foreach ($tenderCompanies as $tenderCompany) {
            $userIds[] = $tenderCompany->company->user->id;
            $userIds[] = $tenderCompany->user_company_id;
        }

        return array_unique($userIds);

        // return array_values(array_unique(TendersCompanies::where('tenders_companies.tender_id', $tender->id)
        //     ->join('companies', 'companies.id', '=', 'tenders_companies.company_id')
        //     ->where('tenders_companies.status','=',TendersCompanies::STATUS_PARTICIPATING)
        //     ->pluck('companies.user_id')
        //     ->all())); 
    }

    public function SelectedMoreWinner(Request $request)
    {
        $tenders_companies_ids   = $request->tenders_companies_ids;

        $id_array = [];

        foreach ($tenders_companies_ids as $tender_company_id) {
            $id_array[] = $tender_company_id['id'];
        }

        $tendersCompanies   = TendersCompanies::whereIn('id', $id_array);
        $tenderVersionLast  = $tendersCompanies->get()->first()->tender->tendersVersionLast();

        DB::beginTransaction();
        $tenderVersionLast->status  = TendersVersions::LICITACION_FINISHED;
        $tendersVersionLastPublish->status  = TendersVersions::LICITACION_FINISHED;

        try {
            $tendersCompanies->update(['winner' => TendersCompanies::WINNER_TRUE]);
            $tenderVersionLast->save();
            $tendersVersionLastPublish->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderError = ['tender' => 'Error, no se ha podido gestionar la solicitud de la licitación'];
            return $this->errorResponse($tenderError, 500);
        }

        $companiesEmails = [];
        foreach ($tendersCompanies->get() as $tenderCompany) {

            $companiesEmails[] = array(
                "company_name"  => $tenderCompany->company->name,
                "email"         => $tenderCompany->company->user->email,
            );

            $companiesEmails[] = array(
                "company_name"  => $tenderCompany->company->name,
                "email"         => $tenderCompany->user->email
            );
        }

        $companiesEmails = $this->unique_multidim_array($companiesEmails, 'email');

        foreach ($companiesEmails as $companyEmail) {
            Mail::to(trim($companyEmail['email']))
                ->send(new SendWinnerTenderCompany($tenderVersionLast->tenders->name, $companyEmail['company_name']));
        }


        return $this->showOne($tenderVersionLast, 200);
    }
}
