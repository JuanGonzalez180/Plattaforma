<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyTenders;

use JWTAuth;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Mail\SendOfferTenderCompany;
use Illuminate\Support\Facades\Mail;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Storage;
use App\Transformers\TendersTransformer;
use App\Mail\SendRetirementTenderCompany;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyTendersController extends ApiController
{
    public $routeFile           = 'public/';
    public $routeTenderCompany  = 'images/tendercompany/';
    //
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug, Request $request)
    {
        // Validamos TOKEN del usuario
        $user           = $this->validateUser();
        // Compañía del usuario que está logueado
        $userCompanyId  = $user->companyId();
        $project_id     = $request->project_id;

        $company = Company::where('slug', $slug)->first();
        if (!$company) {
            $companyError = ['company' => 'Error, no se ha encontrado ninguna compañia'];
            return $this->errorResponse($companyError, 500);
        }

        // Traer Licitaciones
        $userTransform = new UserTransformer();

        $tenders = Tenders::select('tenders.*', 'comp.status AS company_status')
            ->where('tenders.company_id', $company->id)
            ->join('projects', 'projects.id', '=', 'tenders.project_id');

        if ($project_id > 0) {
            $tenders = $tenders->where('projects.id', $project_id);
        };

        $tenders = $tenders->where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->leftjoin('tenders_companies AS comp', function ($join) use ($userCompanyId) {
                $join->on('tenders.id', '=', 'comp.tender_id');
                $join->where('comp.company_id', '=', $userCompanyId);
            })
            ->orderBy('tenders.updated_at', 'desc')
            ->get();

        $company->tenders = $this->getTenderCompany($company->id);

        foreach ($company->tenders as $key => $tender) {
            $user = $tender->user;
            unset($tender->user);
            $tender->user = $user;


            // $version = $tender->tendersVersionLastPublish();
            $version = $tender->tendersVersionLast();
            
            if ($version) {
                $tender->tags = $version->tags;
            }
            $tender->project;
        }

        return $this->showAllPaginate($company->tenders);
    }

    public function show($slug, $id)
    {

        $user           = $this->validateUser();
        // Compañía del usuario que está logueado
        $userCompanyId  = $user->companyId();
        $tender         = Tenders::where('id', $id)->first();

        // Tenders Company
        $company_status = '';
        $tenderCompany = TendersCompanies::where('tender_id', $id)
            ->where('company_id', $userCompanyId)
            ->first();
        if ($tenderCompany && $tenderCompany->status) {
            $company_status = $tenderCompany->status;
        }

        if (!$id || !$tender) {
            $TenderError = ['company' => 'Error, no se ha encontrado ninguna licitación'];
            return $this->errorResponse($TenderError, 500);
        }

        // Traer Licitaciones
        $user = $tender->user;
        unset($tender->user);
        $tender->user = $user;

        $version = $tender->tendersVersionLastPublish();
        if ($version) {
            $tender->tags = $version->tags;
        }

        $tendersTransformer = new TendersTransformer();

        foreach ($tender->tendersVersion as $key => $version) {
            if ($version->status == TendersVersions::LICITACION_CREATED && $slug != $user->companyClass()->slug) {
                unset($tender->tendersVersion[$key]);
            }
        }

        if (
            $company_status == TendersCompanies::STATUS_PARTICIPATING ||
            $company_status == TendersCompanies::STATUS_PROCESS ||
            $slug == $user->companyClass()->slug
        ) {
            foreach ($tender->tendersVersion as $key => $version) {
                $version->files;
            }

            $tender->company_status = $company_status;
            return $this->showOneData($tendersTransformer->transformDetail($tender), 200);
        }

        // Solamente estos datos
        $tender->tendersVersionLastPublish = $tender->tendersVersionLastPublish();
        $tender->categories = $tender->categories;
        $tender->company_status = $company_status;

        return $this->showOne($tender, 200);
    }

    public function edit($id)
    {
        $user = $this->validateUser();

        $tender_company = TendersCompanies::findOrFail($id);
        $tender_company->files;

        $tender_company->tender_value = $tender_company->tender->tendersVersionLast()->price; 


        return $this->showOne($tender_company, 200);
    }

    public function update(Request $request, $slug, $id)
    {
        $rules = [
            'price' => 'required|numeric',
        ];

        $this->validate( $request, $rules );

        $user           = $this->validateUser();
        $tender_company = TendersCompanies::find($id);

        $company = Company::find($user->companyId());
 
        $tender_status  = $tender_company->tender->tendersVersionLast()->status;

        if (($tender_status == TendersVersions::LICITACION_CLOSED) || ($tender_status == TendersVersions::LICITACION_FINISHED)) {
            $tenderCompanyError = ['tenderCompany' => 'Error, la compañia no puede actualizar la licitación, por el motivo que la licitación esta cerrada o finalizada'];
            return $this->errorResponse($tenderCompanyError, 500);
        }

        // if (!in_array($user->id, [$tender_company->user_id, $company->adminCompany()])) {
        //     $tenderCompanyError = ['tenderCompany' => 'Error, permiso para modificar la licitación de la ' . $user->id . 'compañia' . $tender_company->user_id];
        //     return $this->errorResponse($tenderCompanyError, 500);
        // }

        $tenderCompanyFiels['price']        = $request->price;
        $tenderCompanyFiels['commission']  = $request->commission;

        DB::beginTransaction();

        $error = false;
        try {
            $tender_company->update($tenderCompanyFiels);
            DB::commit();
        } catch (\Throwable $th) {
            $error = true;
            // Si existe algún error al actulizar tender-company
            DB::rollBack();
            $companyError = ['tenderCompany' => 'Error, no se ha podido gestionar la actualización'];
            return $this->errorResponse($companyError, 500);
        }

        if(!$error)
        {
            $this->sendEmailTenderCompanyOffer($tender_company);
            $this->sendNotificationTender($tender_company, Notifications::NOTIFICATION_TENDERCOMPANY_OFFER);
        }

        return $this->showOne($tender_company, 200);
    }

    public function sendEmailTenderCompanyOffer($tender_company)
    {
        $emails = [];
        $emails[] = strtolower($tender_company->tender->company->user->email);
        $emails[] = strtolower($tender_company->tender->user->email);

        $emails = array_unique($emails);

        foreach($emails as $email){
            Mail::to($tender_company->tender->company->user->email)
            // Mail::to('cris10x@hotmail.com')
                ->send(new SendOfferTenderCompany(
                    $tender_company->company->name,
                    $tender_company->tender->company->name,
                    $tender_company->price,
                    $tender_company->tender->name
            ));
        }
    }

    public function updateStatusInvitation($slug, $id, $status, $user_id)
    {
        $user           = $this->validateUser();
        
        $tender_company = TendersCompanies::find($id);
        $tender_status  = $tender_company->tender->tendersVersionLast()->status;
        
        if($status == 'true')
        {

            // if(!is_null($user_id))
            // {
            //     $tender_company->user_company_id = $user_id;
            // }

            $tender_company->status = TendersCompanies::STATUS_PARTICIPATING;
            $tender_company->save();

            $this->sendNotificationTender($tender_company, Notifications::NOTIFICATION_INVITATION_APPROVED);

            return $this->showOne($tender_company, 200);
        }
        else
        { 
            $tender_company->delete();
    
            if ($tender_company->files) {
                foreach ($tender_company->files as $key => $file) {
                    Storage::disk('local')->delete($this->routeFile . $file->url);
                    $file->delete();
                }
            }

            //envia los correos al responsable de licitación y al responsable del proyecto
            $this->sendEmailInvitationTender($tender_company);
            //envia los notificaciones al responsable de la licitación y al administrador
            $this->sendNotificationTender($tender_company, Notifications::NOTIFICATION_INVITATION_REJECTED);
    
            return $this->showOneData(
                ['success' => 'Se ha eliminado correctamente.', 'code' => 200]
                , 200
            );
            
        }
    }

    public function sendEmailInvitationTender($tender_company)
    {
            $company_name       = $tender_company->company->name;
            $tender_name        = $tender_company->tender->name;
    
            $emails     = [];
            $emails[]   = $tender_company->tender->user->email; //reponsable de la licitación
            $emails[]   = $tender_company->tender->project->user->email; //responsable del proyecto de la licitación
    
            $emails = array_values(array_unique($emails));
    
            foreach ($emails as $email) {
                Mail::to($email)
                    ->send(new SendRetirementTenderCompany($tender_name, $company_name));
            }
    }

    public function sendNotificationTender($query, $typeNotification)
    {
        $notificationsIds   = [];
        $notificationsIds[] = $query->tender->user_id; // responsable de la licitación
        // $notificationsIds[] = $query->tender->project->user_id; // responsable del proyecto de la licitación
        $notificationsIds[] = $query->tender->company->user_id; //administrador de la compañia

        $notificationsIds   = array_values(array_unique($notificationsIds));

        $notifications      = new Notifications();
        $notifications->registerNotificationQuery($query, $typeNotification, $notificationsIds);
    }

    public function destroy($slug, $id)
    {
        $user           = $this->validateUser();
        $tender_company = TendersCompanies::find($id);
        $tender_status  = $tender_company->tender->tendersVersionLast()->status;

        if (($tender_status == TendersVersions::LICITACION_CLOSED) || ($tender_status == TendersVersions::LICITACION_FINISHED)) {
            $tenderCompanyError = ['tenderCompany' => 'Error, la compañia no se puede retirar de la licitacion, por el motivo que la licitación esta cerrada o finalizada'];
            return $this->errorResponse($tenderCompanyError, 500);
        }

        // Revisar consulta.
        if ($user->company[0]->id != $tender_company->company->id) {
            $tenderCompanyError = ['tenderCompany' => 'Error, el usuario no tiene permiso para borrar la licitación de la compañia'];
            return $this->errorResponse($tenderCompanyError, 500);
        }

        $tender_company->delete();

        if ($tender_company->files) {
            foreach ($tender_company->files as $key => $file) {
                Storage::disk('local')->delete($this->routeFile . $file->url);
                $file->delete();
            }
        }

        $company_name       = $tender_company->company->name;
        $tender_name        = $tender_company->tender->name;

        $emails     = [];
        $emails[]   = $tender_company->tender->user->email;
        $emails[]   = $tender_company->tender->project->user->email;

        $emails = array_values(array_unique($emails));

        foreach ($emails as $email) {
            Mail::to($email)
                ->send(new SendRetirementTenderCompany($tender_name, $company_name));
        }

        // Enviar invitación por notificación
        $this->sendNotificationTender($tender_company, Notifications::NOTIFICATION_TENDERCOMPANYNOPARTICIPATE);

        return $this->showOneData(['success' => 'Se ha eliminado correctamente.', 'code' => 200], 200);
    }

    public function getTenderCompany($company_id)
    {
        return Tenders::where('company_id',$company_id)
            ->whereIn('id',$this->getTendersPublish())
            ->get();
    }

    public function getTendersPublish()
    {
        return DB::table('tenders_versions as a')
            ->select(DB::raw('max(a.created_at), a.tenders_id'))
            ->where('a.status', TendersVersions::LICITACION_PUBLISH)
            ->where((function ($query) {
                $query->select(
                    DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where `b`.`status` != '" . TendersVersions::LICITACION_PUBLISH . "'  
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->pluck('a.tenders_id');
    }
}
