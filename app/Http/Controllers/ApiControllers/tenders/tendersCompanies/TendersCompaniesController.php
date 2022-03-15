<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersCompanies;

use JWTAuth;
use App\Models\Team;
use App\Models\Tenders;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendRespondTenderCompany;
use App\Mail\SendInvitationTenderCompany;
use App\Mail\SendUpdateTenderCompany;
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

        $tender = Tenders::where('id', $tender_id)->first();
        $version = $tender->tendersVersionLastPublish();

        if($user->userType() != 'demanda' && ($version->status==TendersVersions::LICITACION_CREATED || $version->status==TendersVersions::LICITACION_PUBLISH) ){
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

    public function store( Request $request ) //envia invitaciones a la licitación
    {
        $tender_id = $request->tender_id;
        $companies = $request->companies_id;

        $user = $this->validateUser();
        
        DB::beginTransaction();

        //Licitación
        $tender                 = Tenders::findOrFail($tender_id);
        //compañias que ya estan participando
        $tendersCompaniesOld    = $tender->tenderCompanies; 
        //registra las nuevas compañias a la licitación y obtiene una arreglo de las nuevas compañias
        $tendersCompaniesNew    = ($companies)? $this->createTenderCompanies($tender, $companies) : []; 
        
        //Actualiza el estado de la licitación
        $tenderVersion          = $tender->tendersVersionLast();
        $tenderVersion->status  = TendersVersions::LICITACION_PUBLISH;
        $tenderVersion->save();
        DB::commit();

        //Envia correos y notificaciones de invitación nuevas a participar
        $this->sendMessageTenderInvitation($tendersCompaniesNew, $tender);
        
        //Envia correos y notificaciones a las compañia ya participantes
        $this->sendMessageTenderVersión($tendersCompaniesOld, $tender);
     
        return $this->showOne($tender,201);
    }

    public function sendMessageTenderInvitation($tenderCompanies, $tender)
    {
        $notifications = new Notifications();
        
        foreach ($tenderCompanies as $key => $tenderCompany)
        {
            //envia las notificaciones a los usuarios por compañia participante
            $notifications->registerNotificationQuery( $tender, Notifications::NOTIFICATION_TENDERINVITECOMPANIES, $this->getTeamsCompanyIds($tenderCompany) );
            //envia los correos a los usuarios por compañia participante
            $this->sendEmailTenderInvitation($this->getTeamsCompanyEmails($tenderCompany), $tenderCompany);
        }
    }
    
    public function sendMessageTenderVersión($tenderCompanies, $tender)
    {
        $notifications = new Notifications();

        foreach ($tenderCompanies as $key => $tenderCompany)
        {
            if($tenderCompany->status == TendersCompanies::STATUS_PARTICIPATING)
            {
                //envia las notificaciones a los usuarios por compañia participante
                $notifications->registerNotificationQuery( $tender, Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION, $this->getTeamsCompanyIds($tenderCompany) );
                //envia los correos a los usuarios por compañia participante
                $this->sendEmailTenderVersion($this->getTeamsCompanyEmails($tenderCompany), $tenderCompany);
            }
        }
    }

    public function sendEmailTenderVersion($UserEmails, $tenderCompany)
    {
        foreach($UserEmails as $mail)
        {
            Mail::to($mail)->send(new SendUpdateTenderCompany(
                $tenderCompany->tender->name, 
                $tenderCompany->tender->tendersVersionLast()->adenda, 
                $tenderCompany->company->name
            ));
        }
    }
    
    public function sendEmailTenderInvitation($UserEmails, $tenderCompany)
    {
        foreach($UserEmails as $mail)
        {
            Mail::to($mail)->send(new SendInvitationTenderCompany(
                $tenderCompany->tender->name, 
                $tenderCompany->tender->tendersVersionLast()->adenda, 
                $tenderCompany->company->name
            ));
        }
    }

    public function createTenderCompanies($tender, $companies)
    {
        $user = $this->validateUser();
        $tendersCompanies = [];

        foreach($companies as $company)
        {
            $tenderCompanyFields['tender_id']   = $tender->id;
            $tenderCompanyFields['company_id']  = $company["id"];
            $tenderCompanyFields['user_id']     = $user->id;
            $tenderCompanyFields['status']      = TendersCompanies::STATUS_PROCESS;
            
            $tendersCompanies[] = TendersCompanies::create( $tenderCompanyFields );
        }

        return $tendersCompanies;
    }

    public function getTeamsCompanyIds($tenderCompany)
    {
        $admin = $tenderCompany->company->user->id;
        $teams = Team::where('company_id', $tenderCompany->company->id)
            ->where('status', Team::TEAM_APPROVED)
            ->pluck('user_id')
            ->all();

        return array_merge([$admin], $teams);
    }

    public function getTeamsCompanyEmails($tenderCompany)
    {
        $admin = $tenderCompany->company->user->email;
        $teams = Team::where('teams.company_id', $tenderCompany->company->id)
            ->where('teams.status', Team::TEAM_APPROVED)
            ->join('users', 'users.id', '=', 'teams.user_id')
            ->pluck('users.email')
            ->all();

        return array_merge([$admin], $teams);
    }

    public function sendEmailInvitationCompany($tenderCompany)
    {

    }

    public function show($id)
    {
        
    }

    public function update(Request $request, $id)
    {
        // die;
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

        // Enviar invitación por notificación
        $notificationsIds = [];
        $notificationsIds[] = $tenderCompany->company->user->id;
        $notifications = new Notifications();
        $notifications->registerNotificationQuery( $tenderCompany, Notifications::NOTIFICATION_TENDERRESPONSECOMPANIES, $notificationsIds );

        return $this->showOne($tenderCompany,200);

    }

    public function destroy($id)
    {
        

    }
}
