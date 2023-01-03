<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersCompanies;

use JWTAuth;
use App\Models\Tags;
use App\Models\User;
use App\Models\Tenders;
use App\Models\Company;
use APP\Models\TemporalRecomendation;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Traits\UsersCompanyTenders;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendUpdateTenderCompany;
use App\Mail\sendRespondTenderCompany;
use App\Mail\sendRecommentTenderCompany;
use App\Models\TemporalInvitationCompany;
use App\Mail\SendInvitationTenderCompany;
use App\Http\Controllers\ApiControllers\ApiController;

use Illuminate\Support\Facades\Storage;

class TendersCompaniesController extends ApiController
{
    use UsersCompanyTenders;

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();
        $tender_id = $request->tender_id;

        $tender = Tenders::where('id', $tender_id)->first();
        $version = $tender->tendersVersionLastPublish();

        if ($user->userType() != 'demanda' && ($version->status == TendersVersions::LICITACION_CREATED || $version->status == TendersVersions::LICITACION_PUBLISH)) {
            $companyError = ['company' => 'Error, El usuario no puede listar las compañias participantes'];
            return $this->errorResponse($companyError, 500);
        }

        $companies = TendersCompanies::select('tenders_companies.*', 'images.url')
            ->join('companies', 'companies.id', '=', 'tenders_companies.company_id')
            ->leftJoin('images', function ($join) {
                $join->on('images.imageable_id', '=', 'companies.id');
                $join->where('images.imageable_type', '=', Company::class);
            })
            ->where('tenders_companies.tender_id', $tender_id)
            ->get();

        return $this->showAllPaginate($companies);
    }

    public function store(Request $request) //envia invitaciones a la LICITACIÓN
    {
        $tender_id = $request->tender_id;

        $user = $this->validateUser();

        DB::beginTransaction();
        // 1. optiene información de la LICITACIÓN.
        $tender                 = Tenders::findOrFail($tender_id);
        // 2. Registra las compañias nuevas a participar y devuelve un array de IDS de las compañias nuevas.
        $tendersCompaniesNew    = ($request->companies_id) ? $this->createTenderCompanies($tender, $request->companies_id) : [];
        // 3. cambia el estado de la LICITACIÓN a 'PUBLICADA'.
        $tenderVersion          = $tender->tendersVersionLast();
        $tenderVersion->status  = TendersVersions::LICITACION_PUBLISH;
        $tenderVersion->save();
        DB::commit();
        // 4. Envia correos de invitación a las compañias nuevas a participar.
        $this->sendMessageTenderInvitation($tendersCompaniesNew, $tender);
        // 5. Guarda en una tabla temporal los correos de invitación para luego ser enviados.
        if ($request->companies_email) {
            $this->sendInvitantionExternalCompanies($request->companies_email, $tender);
        }

        $tender->participatingUsers     = $tender->TenderCompanyIdUsers();
        $tender->companyName            = $tender->company->name;
        $tender->projectName            = $tender->project->name;

        $this->sendRecommendTender($tender);

        return $this->showOne($tender, 201);
    }

    public function store_old(Request $request) //envia invitaciones a la licitación
    {
        $tender_id = $request->tender_id;

        $user = $this->validateUser();

        DB::beginTransaction();

        //Licitación
        $tender                 = Tenders::findOrFail($tender_id);

        //compañias que ya estan participando
        $tendersCompaniesOld    = $tender->tenderCompanies;

        //registra las nuevas compañias a la licitación y obtiene una arreglo de las nuevas compañias
        $tendersCompaniesNew    = ($request->companies_id) ? $this->createTenderCompanies($tender, $request->companies_id) : [];


        $companies = $tendersCompaniesOld->merge($tendersCompaniesNew);


        //Actualiza el estado de la licitación
        $tenderVersion          = $tender->tendersVersionLast();
        $tenderVersion->status  = TendersVersions::LICITACION_PUBLISH;
        $tenderVersion->save();
        DB::commit();

        //Envia correos y notificaciones de invitación nuevas a participar
        $this->sendMessageTenderInvitation($tendersCompaniesNew, $tender);

        //Envia correos y notificaciones a las compañia ya participantes
        $this->sendMessageTenderVersión($tendersCompaniesOld, $tender);

        //Envia correos de invitación a compañia que no estan registradas en plattaforma
        if ($request->companies_email) {
            $this->sendInvitantionExternalCompanies($request->companies_email, $tender);
        }

        // enviar correos y notificaciones a compañias registradas pero no invitadas
        // teniendo en comun las etiquetas de la licitacion y de las compañias
        $this->sendRecommendTender_old($tender, $companies);


        return $this->showOne($tender, 201);
    }

    public function sendInvitantionExternalCompanies($emails, $tender)
    {
        foreach ($emails as $key => $email) {
            if (!($this->emailExistUser($email)) && !($this->invitationTenderExist($email, $tender))) {
                $fields['tender_id']    = $tender->id;
                $fields['email']        = $email;

                TemporalInvitationCompany::create($fields);
            }
        }
    }

    public function sendRecommendTender($tender)
    {
        $tags = $tender->tendersVersionLast()->tagsName();

        $companies = $tender->tenderCompaniesIds();

        $recommendToCompanies = ($tender->type == 'Publico') ? $this->getQueryCompaniesTags($tags, $companies) : [];

        if ((sizeof($recommendToCompanies) > 0) && (count($tender->tendersVersion) == 1)) {
            foreach ($recommendToCompanies as $key => $value) {
                $company = Company::find($value);
                $this->sendNotificationRecommendTender($tender, $company->userIds());
                DB::table('temporal_recommendation')->insert([
                    'modelsable_id'     => $tender->id,
                    'modelsable_type'   => Tenders::class,
                    'company_id'        => $company->id,
                ]);
            }
        }
    }

    public function sendRecommendTender_old($tender, $companies)
    {
        $companiesNew = [];

        $tags = $tender->tendersVersionLast()->tagsName();

        foreach ($companies as $key => $value) {
            $companiesNew[] = $value['id'];
        }

        $recommendToCompanies = ($tender->type == 'Publico') ? $this->getQueryCompaniesTags($tags, $companiesNew) : [];

        //si por lo menos existe alguna compañia con alguna etiqueta
        if (sizeof($recommendToCompanies) > 0) {
            foreach ($recommendToCompanies as $key => $value)
            {
                $company = Company::find($value);
                $this->sendNotificationRecommendTender($tender, $company->userIds());
                // $this->sendEmailRecommendTender($tender, ['davidmejia13320@gmail.com']);
            }
        }
    }

    public function sendNotificationRecommendTender($tender, $users)
    {
        $notifications = new Notifications();
        $notifications->registerNotificationQuery($tender, Notifications::NOTIFICATION_RECOMMEND_TENDER, $users);
    }

    public function sendEmailRecommendTender($tender, $emails)
    {
        foreach ($emails as $key => $value) {
            Mail::to(trim($value))->send(new sendRecommentTenderCompany(
                $tender->name,
                $tender->company->name,
                $tender->company->slug,
                $tender->id,
            ));
        }
    }

    public function getQueryCompaniesTags($tags, $companies)
    {
        return Tags::where('tagsable_type', Company::class)
            ->where(function ($query) use ($tags) {
                for ($i = 0; $i < count($tags); $i++) {
                    $query->orwhere(strtolower('tags.name'), 'like', '%' . strtolower($tags[$i]) . '%');
                }
            })
            ->join('companies', 'companies.id', '=', 'tags.tagsable_id')
            ->whereNotIn('companies.id', $companies)
            ->where('companies.status', 'Aprobado')
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.name', '=', 'Oferta')
            ->orderBy('companies.id', 'asc')
            ->distinct()
            ->pluck('companies.id');
    }

    public function invitationTenderExist($email, $tender)
    {
        return TemporalInvitationCompany::where('tender_id', '=',  $tender->id)
            ->where(strtolower('email'), '=', strtolower($email))
            ->where('send', '=', false)
            ->exists();
    }

    public function emailExistUser($email)
    {
        return User::where('email', '=', $email)
            ->exists();
    }

    public function sendMessageTenderInvitation($tenderCompanies, $tender)
    {
        $notifications = new Notifications();

        foreach ($tenderCompanies as $key => $tenderCompany) {
            //1. NOTIFICACIONES -> Envia las notificaciones a los usuarios por compañia participante.
            $notifications->registerNotificationQuery(
                $tender,
                Notifications::NOTIFICATION_TENDERINVITECOMPANIES,
                [$tenderCompany->company->user->id]
            );
            //2. CORREOS -> Envia los correos a los usuarios al usuario administrador de la compañia licitante.
            $this->sendEmailTenderInvitation(
                [$tenderCompany->company->user->email],
                $tenderCompany
            );
        }
    }

    public function sendMessageTenderVersión($tenderCompanies, $tender)
    {
        $notifications = new Notifications();

        foreach ($tenderCompanies as $key => $tenderCompany) {
            if ($tenderCompany->status == TendersCompanies::STATUS_PARTICIPATING) {
                //*ANTES - ENVIA CORREOS Y NOTIFICACIONES A TODOS LOS USUARIOS POR COMPAÑIAS YA PARTICIPANTES A LA LICITACION.

                //1. NOTIFICACIONES -> Envia las notificaciones a los usuarios por compañia participante
                //$notifications->registerNotificationQuery($tender, Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION, $this->getTeamsCompanyUsers($tenderCompany->company, 'id'));
                //2. CORREOS -> Envia los correos a los usuarios por compañia participante
                //$this->sendEmailTenderVersion($this->getTeamsCompanyUsers($tenderCompany->company, 'email'), $tenderCompany);


                //*DESPUES - ENVIA CORREOS Y NOTIFICACIONES A SOLO LOS USUARIOS ENCARTGADOS DE LA LICITACIÓN Y ADMINISTRADOR POR COMPAÑIA.

                //1. NOTIFICACIONES -> Envia las notificaciones a los usuarios por compañia participante
                $notifications->registerNotificationQuery(
                    $tender,
                    Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION,
                    [
                        $tenderCompany->userCompany->id,
                        $tenderCompany->company->user->id
                    ]
                );
                //2. CORREOS -> Envia los correos a los usuarios por compañia participante
                $this->sendEmailTenderVersion(
                    [
                        $tenderCompany->userCompany->email,
                        $tenderCompany->company->user->email
                    ],
                    $tenderCompany
                );
            }
        }
    }

    public function sendEmailTenderVersion($UserEmails, $tenderCompany)
    {
        foreach ($UserEmails as $mail) {
            Mail::to(trim($mail))->send(new SendUpdateTenderCompany(
                $tenderCompany->tender->name,
                $tenderCompany->tender->tendersVersionLast()->adenda,
                $tenderCompany->company->name
            ));
        }
    }

    public function sendEmailTenderInvitation($UserEmails, $tenderCompany)
    {
        foreach ($UserEmails as $mail) {
            Mail::to(trim($mail))->send(new SendInvitationTenderCompany(
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

        foreach ($companies as $company) {
            $userCompanyId = Company::find($company["id"])->user->id;

            $tenderCompanyFields['tender_id']           = $tender->id;
            $tenderCompanyFields['company_id']          = $company["id"];
            $tenderCompanyFields['user_id']             = $user->id;
            $tenderCompanyFields['user_company_id']     = $userCompanyId;
            $tenderCompanyFields['status']              = TendersCompanies::STATUS_PROCESS;

            $tendersCompanies[] = TendersCompanies::create($tenderCompanyFields);
        }

        return $tendersCompanies;
    }

    public function show($id)
    {
    }

    public function update(Request $request, $id)
    {
        $user = $this->validateUser();
        $status = ($request->status == 'True') ? TendersCompanies::STATUS_PARTICIPATING : TendersCompanies::STATUS_REJECTED;

        if ($user->userType() != 'demanda') {
            $companyError = ['tenderCompany' => 'Error, El usuario no puede gestionar la validacion de la compañia hacia la licitación'];
            return $this->errorResponse($companyError, 500);
        }

        $tenderCompany = TendersCompanies::find($id);
        // Iniciar Transacción
        DB::beginTransaction();

        $tenderCompany->status = $status;

        try {
            $tenderCompany->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderCompanyError = ['tender' => 'Error, no se ha podido gestionar la solicitud de la compañia'];
            return $this->errorResponse($tenderCompanyError, 500);
        }

        DB::commit();

        $email          = $tenderCompany->company->user->email;
        $tender_name    = $tenderCompany->tender->name;
        $company_name   = $tenderCompany->company->name;

        //  Email
        Mail::to($email)->send(new sendRespondTenderCompany(
            $tender_name,
            $company_name,
            $status
        ));

        //  Notificaciones
        $notificationsIds = [];
        $notificationsIds[] = $tenderCompany->company->user->id;
        $notifications = new Notifications();
        $notifications->registerNotificationQuery($tenderCompany, Notifications::NOTIFICATION_TENDERRESPONSECOMPANIES, $notificationsIds);

        return $this->showOne($tenderCompany, 200);
    }

    public function destroy($id)
    {
    }
}