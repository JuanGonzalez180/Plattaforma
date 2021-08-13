<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersAction;

use JWTAuth;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\QueryWall;
use App\Models\Notifications;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendDeclinedTenderCompany;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersActionController extends ApiController
{
    //
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function updateTenderUser(Request $request, $id)
    {
        $user = $this->validateUser();

        if($user->userType() != 'demanda'){
            $tenderError = [ 'querywall' => 'Error, El usuario no puede editar la licitación' ];
            return $this->errorResponse( $tenderError, 500 );
        }

        $tender = Tenders::find($id);

        //verifica si el usuario es el responsabel del proyecto
        $project_admin = ($tender->project->user_id == $user->id) ? True : False;

        //verifica si el usuario es el admnistrador de la compañia
        $company_admin = ($tender->company->user_id == $user->id) ? True : False;


        if($project_admin || $company_admin) {

            DB::beginTransaction();

            $tenderFields['user_id'] = $request['user_id'];

            try{
                $tender->update( $tenderFields );
            } catch (\Throwable $th) {
                // Si existe algún error al momento de editar el tender
                DB::rollBack();
                $tenderError = [ 'tender' => 'Error, no se ha podido editar la licitación'];
                return $this->errorResponse( $tenderError, 500 );
            }

            DB::commit();

        } else {
            $tenderError = [ 'querywall' => 'Error, El usuario no tiene privilegios para asignar un responsable a una licitación' ];
            return $this->errorResponse( $tenderError, 500 );
        }

        return $this->showOne($tender,200);

    }

    public function updateStatusClosed($id)
    {
        $tenderVersionLast = Tenders::find($id)->tendersVersionLast();

        DB::beginTransaction();

        $tenderVersionLast->status = TendersVersions::LICITACION_CLOSED;

        try{
            $tenderVersionLast->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderVersionError = [ 'tenderVersionLast' => 'Error, no se ha podido gestionar la solicitud de la licitación'];
            return $this->errorResponse( $tenderVersionError, 500 );
        }

        return $this->showOne($tenderVersionLast,200);
    }

    public function updateStatusDeclined($id)
    {
        $tender            = Tenders::find($id);
        $tenderVersionLast = $tender->tendersVersionLast();

        DB::beginTransaction();

        $tenderVersionLast->status = TendersVersions::LICITACION_DECLINED;

        try{
            $tenderVersionLast->save();
            DB::commit();
            $companies  = $tender->tenderCompanies;
            $notificationsIds = [];

            foreach ($companies as $companyTender){
                $user = $companyTender->user;
                $company = $companyTender->company;
                // Informar por correo a los participantes que se ha declinado la licitación.
                Mail::to($company->user->email)->send(new SendDeclinedTenderCompany($tender->name, $company->name));
                array_push($notificationsIds, $company->user->id);
            }
            
            $notifications = new Notifications();
            $notifications->registerNotificationQuery( $tender, Notifications::NOTIFICATION_TENDERSDECLINED, $notificationsIds );

        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderVersionError = [ 'tenderVersionLast' => 'Error, no se ha podido gestionar la solicitud de la licitación' ];
            return $this->errorResponse( $tenderVersionError, 500 );
        }

        return $this->showOne($tenderVersionLast,200);
    }

}
