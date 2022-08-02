<?php

namespace App\Http\Controllers\ApiControllers\tenders;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Projects;
use App\Models\Proponents;
use App\Models\Advertisings;
use Illuminate\Http\Request;
use App\Traits\DeleteRecords;
use App\Models\TendersVersions;
use App\Models\CategoryTenders;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Models\RegistrationPayments;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendDeleteTenderCompany;
use App\Models\TemporalInvitationCompany;
use App\Models\AdvertisingPlansPaidImages;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersController extends ApiController
{
    use DeleteRecords;

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
    public function index(Request $request)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $rules = [
            'project' => 'nullable|numeric',
        ];

        $this->validate($request, $rules);

        //IS ADMIN
        $companyID = $user->companyId();

        if ($companyID && $user->userType() == 'demanda') {
            if ($user->isAdminFrontEnd()) {
                $tenders = Tenders::select('tenders.id', 'tenders.name', 'tenders.type', 'tenders.description', 'tenders.project_id', 'tenders.company_id', 'tenders.user_id', 'tenders.date_update', 'tenders.created_at', 'tenders.updated_at')
                    ->where('tenders.company_id', $companyID);
            } else {
                $tenders = Tenders::select('tenders.id', 'tenders.name', 'tenders.type', 'tenders.description', 'tenders.project_id', 'tenders.company_id', 'tenders.user_id', 'tenders.date_update', 'tenders.created_at', 'tenders.updated_at')
                    ->where('tenders.user_id', $user->id)
                    ->where('tenders.company_id', $companyID);
            }

            if ($request->project) {
                $tenders = $tenders->where('project_id', $request->project);
            }

            // Filtrar por orden desc or asc
            if ($request->orderby && $request->order) {
                $tenders = $tenders->join('tenders_versions AS tversion', function ($join) {
                    $join->on('tenders.id', '=', 'tversion.tenders_id');
                    $join->on('tversion.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM tenders_versions WHERE tenders_id=tversion.tenders_id)'));
                });
                if ($request->orderby == 'date') {
                    $tenders = $tenders->orderBy('tversion.date', $request->order);
                } elseif ($request->orderby == 'price') {
                    $tenders = $tenders->orderBy('tversion.price', $request->order);
                }
            } else {
                $tenders = $tenders->orderBy('tenders.updated_at', 'desc');
            }

            if ($request->filter) {
                $tenders = $tenders->join('projects', function ($join) use ($request) {
                    $join->on('tenders.project_id', '=', 'projects.id')
                        ->where(strtolower('tenders.name'), 'LIKE', '%' . strtolower($request->filter) . '%')
                        ->orWhere(strtolower('projects.name'), 'LIKE', '%' . strtolower($request->filter) . '%');
                });
            }

            $tenders = $tenders->groupBy('tenders.id', 'tenders.name', 'tenders.type', 'tenders.description', 'tenders.project_id', 'tenders.company_id', 'tenders.user_id', 'tenders.date_update', 'tenders.created_at', 'tenders.updated_at')
                ->get();

            return $this->showAllPaginate($tenders);
        }
        return [];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $this->validateUser();

        $rules = [
            'name'          => 'required',
            'description'   => 'required',
            'price'         => 'required|numeric',
            'project'       => 'required|numeric',
            'date'          => 'required',
            'hour'          => 'required',
            'tender_type'   => 'required'
        ];

        $this->validate($request, $rules);

        $project_date_end   = Carbon::parse(Projects::find($request['project'])->date_end);
        $tender_date_end    = Carbon::parse(date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day'])));

        if ($tender_date_end->greaterThan($project_date_end)) {
            $tenderError = ['tender' => 'Error, La fecha de cierre de la licitacion debe ser menor a la fecha de cierre del proyecto'];
            return $this->errorResponse($tenderError, 500);
        }

        // Iniciar Transacción
        DB::beginTransaction();

        // Datos
        $tendersFields['name']          = $request['name'];
        $tendersFields['description']   = $request['description'];
        $tendersFields['user_id']       = $request['user'] ?? $user->id;
        $tendersFields['company_id']    = $user->companyId();
        $tendersFields['project_id']    = $request['project'];
        $tendersFields['type']          = $request['tender_type'];
        // El campo Adenda quedará igual al nombre de la licitación por primera vez.
        $tendersVersionFields['adenda'] = $request['name'];
        $tendersVersionFields['price'] = $request['price'];
        if ($request['date']) {
            $tendersVersionFields['date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
        }
        if ($request['hour']) {
            $tendersVersionFields['hour'] = $this->timeFormat($request['hour']['hour']) . ':' . $this->timeFormat($request['hour']['minute']);
        }

        try {
            $tender = Tenders::create($tendersFields);

            $tendersVersionFields['tenders_id'] = $tender->id;
            $tendersVersions = TendersVersions::create($tendersVersionFields);

            // Crear Tenders
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorTender = true;
            DB::rollBack();
            $tenderError = ['tender' => 'Error, no se ha podido crear el tenders'];
            return $this->errorResponse($tenderError, 500);
        }

        if ($tender) {
            if ($request->categories) {
                foreach ($request->categories as $key => $categoryId) {
                    $tender->tenderCategories()->attach($categoryId);
                }
            }

            foreach ($request->tags as $key => $tag) {
                $tendersVersions->tags()->create(['name' => $tag['displayValue']]);
            }

            $tender->tendersVersions = $tendersVersions;
        }
        DB::commit();


        // * Notifica al integrate del equipo encargado de la licitación

        return $this->showOne($tender, 201);
    }

    // public function formatDate


    public function timeFormat($value)
    { 
        return (strlen((string)$value) == 1) ? str_pad($value,2,"0", STR_PAD_LEFT) : $value; 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function companies($id)
    {
        return TendersCompanies::select('companies.id', 'companies.name', 'images.url')
            ->join('companies', 'companies.id', '=', 'tenders_companies.company_id')
            ->leftJoin('images', function ($join) {
                $join->on('images.imageable_id', '=', 'companies.id');
                $join->where('images.imageable_type', '=', Company::class);
            })
            ->where('tenders_companies.tender_id', $id)
            ->get();
    }

    public function show($id)
    {
        $user = $this->validateUser();

        $tender = Tenders::findOrFail($id);
        $tender->categories;
        $tender->tendersVersion;
        $tender->tenderCompanies;

        $tender->companies = $this->companies($tender->id);

        if ($tender->tendersVersion) {
            foreach ($tender->tendersVersion as $key => $tenderVersion) {
                $tenderVersion->tags;
                $tenderVersion->files;
            }
        }
        $tender->tendersVersionLast = $tender->tendersVersionLast();

        return $this->showOne($tender, 201);
    }

    public function edit($id)
    {
        //
        $user = $this->validateUser();

        $tender = Tenders::findOrFail($id);
        $tender->user;
        $tender->categories;
        $tender->version_last = $tender->tendersVersionLast();
        $tender->version_last->tags;
        $tender->version_last->files;

        return $this->showOne($tender, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $count = TendersVersions::where('tenders_id', $id)
            ->where('status', '=', TendersVersions::LICITACION_CREATED)
            ->get()
            ->count();

        if ($count == 1) {
            $user = $this->validateUser();

            $rules = [
                //tender
                'name' => 'required',
                'description' => 'required',
                'project' => 'required|numeric',
                //tender_version
                'price' => 'required|numeric',
                'project' => 'required|numeric',
                'date' => 'required',
                'hour' => 'required'
            ];

            $this->validate($request, $rules);


            DB::beginTransaction();

            $tender = Tenders::findOrFail($id);

            //tender
            $tenderFields['name']           = $request['name'];
            $tenderFields['description']    = $request['description'];
            $tenderFields['project_id']     = $request['project'];
            $tenderFields['company_id']     = $user->companyId();
            $tenderFields['user_id']        = $request['user'] ?? $user->id;

            //tender_version
            $tenderVersionFields['adenda']  = $request['adenda'];
            $tenderVersionFields['price']   = $request['price'];

            if ($request['date']) {
                $tenderVersionFields['date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
            }
            if ($request['hour']) {
                $tenderVersionFields['hour'] = $request['hour']['hour'] . ':' . $request['hour']['minute'];
            }

            try {
                $tender->update($tenderFields);

                // Categorías
                // Eliminar los anteriores
                foreach ($tender->tenderCategories as $key => $category) {
                    $tender->tenderCategories()->detach($category->id);
                }

                if ($request->categories) {
                    foreach ($request->categories as $key => $categoryId) {
                        $tender->tenderCategories()->attach($categoryId);
                    }
                }

                $tenderVersion = TendersVersions::where('tenders_id', $id)
                    ->where('status', '=', TendersVersions::LICITACION_CREATED)
                    ->get()
                    ->first();

                $tenderVersion->update($tenderVersionFields);

                // Tags
                // Eliminar los anteriores
                foreach ($tenderVersion->tags as $key => $tag) {
                    $tag->delete();
                }

                foreach ($request->tags as $key => $tag) {
                    $tenderVersion->tags()->create(['name' => $tag['displayValue']]);
                }

                $tender->tendersVersions = $tenderVersion;
            } catch (\Throwable $th) {
                // Si existe algún error al momento de editar el tender
                $errorTender = true;
                DB::rollBack();
                $tenderError = ['tender' => 'Error, no se ha podido editar la licitación'];
                return $this->errorResponse($tenderError, 500);
            }

            DB::commit();

            return $this->showOne($tender, 200);
        } else {
            $tenderError = ['tender' => 'Error, no se ha podido editar la licitación, tiene más versiones.'];
            return $this->errorResponse($tenderError, 500);
        }

        return [];
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tender         = Tenders::find($id);
        //Verifica si la licitación no existe para lanzar mensaje de error.
        if (!$tender) {
            return $this->errorResponse('La licitación no existe o ya ha sido eliminada.', 500);
        }

        DB::beginTransaction();
        $errorTender = false;
        try {
            $tenderStatus   = $tender->tendersVersionLast()->status;
            //-----1. borra todos los registros de compañias licitantes
            $tenderCompanies          = $tender->tenderCompanies->pluck('id');
            $companiesParticipate     = $this->getCompaniesParticipating($tenderCompanies);
            $this->deleteAllTenderCompanies($tenderCompanies);
            //-----2. Borra todas las versiones de la licitación-----
            $tenderVersions     = $tender->tendersVersion->pluck('id');
            $this->deleteAllTenderVersions($tenderVersions);
            //-----3. Borra los proponentes-----
            $this->deleteTenderProponents($tender->id);
            //-----4. Borra las categorias de la licitación-----
            $this->deleteCategoryTenders($tender->id);
            // -----5. Borra la publicidad/es de la licitación-----
            // $this->deleteAllAdvertising($tender->id);
            // -----6.borrar las invitaciones de licitaciones a compañias-----
            $this->deleteInvitationCompany($tender->id);
            // -----7.borrar los datos de la licitación-----
            $this->deleteAllTender($tender->id);
            $tender->delete();
        } catch (\Throwable $th) {
            DB::rollBack();
            $errorTender = true;
            $tenderError = ['user' => 'Error, no se ha podido borrar la licitación'];
            return $this->errorResponse($tenderError, 500);
        }

        if (!$errorTender) {
            DB::commit();

            $this->sendDeleteTenderCompanyEmail($tender->name, $companiesParticipate);
        }

        return $this->showOne($tender, 200);
    }

    public function deleteInvitationCompany($tender_id)
    {
        TemporalInvitationCompany::where('tender_id',$tender_id)->delete();
    }

    public function getTendersCompanies($tender)
    {
        return TendersCompanies::where('tenders_companies.tender_id', $tender->id)
            ->join('companies', 'companies.id', '=', 'tenders_companies.company_id')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->where('tenders_companies.status', '=', TendersCompanies::STATUS_PARTICIPATING)
            ->pluck('companies.id')
            ->all();
    }


    public function deleteAllAdvertising($tender_id)
    {
        $advertisings = Advertisings::where('advertisingable_id', $tender_id)
            ->where('advertisingable_type', Tenders::class)
            ->pluck('id');

        foreach ($advertisings as $value) {
            //borra los registros de facturación del la publicidad.
            $this->deleteAllRegistPayment([$value], Advertisings::class);
            //borra los registros y archivos de AdvertisingPlansPaidImages.
            $this->deleteAdvertisingPlansPaidImages([$value]);
            //borra los registros de publicidad.
            Advertisings::destroy([$value]);
        }
    }

    public function deleteAdvertisingPlansPaidImages($advertisingId)
    {
        $planPaidImages = AdvertisingPlansPaidImages::whereIn('advertisings_id', $advertisingId)
            ->pluck('id');

        foreach ($planPaidImages as $value) {
            //borra las imagenes del la publicidad
            $this->deleteImage([$value], AdvertisingPlansPaidImages::class);
            //borra los registros de AdvertisingPlansPaidImages
            AdvertisingPlansPaidImages::destroy([$value]);
        }
    }

    public function sendDeleteTenderCompanyEmail($tenderName, $companies)
    {
        foreach ($companies as $value) {
            Mail::to(trim($value['email_admin']))->send(new SendDeleteTenderCompany($tenderName, $value['company']));
            if ($value['email_admin'] != $value['email_responsible']) {
                Mail::to(trim($value['email_responsible']))->send(new SendDeleteTenderCompany($tenderName, $value['company']));
            }
        }
    }

    public function getCompaniesParticipating($tenderCompanies)
    {
        $tendersCompanies =  TendersCompanies::whereIn('id', $tenderCompanies)
            ->where('status', TendersCompanies::STATUS_PARTICIPATING)
            ->get();

        $companies = [];

        foreach ($tendersCompanies as $key => $value) {
            $companies[$key]['company']             = $value->company->name;
            $companies[$key]['email_responsible']   = $value->user->email;
            $companies[$key]['email_admin']         = $value->company->user->email;
        }

        return $companies;
    }

    public function deleteAllTender($tender_id)
    {
        $this->deleteInterests([$tender_id], Tenders::class);
        //Borra los remarks de tender
        $this->deleteRemarks([$tender_id], Tenders::class);
        //borra las notificaciones de tender
        $this->deleteNotifications([$tender_id], Tenders::class);
        //borra las querywalls de tender
        $this->deleteQueryWall([$tender_id], Tenders::class);
    }

    public function deleteAllTenderCompanies($ids)
    {
        //borra los archivos de tendersCompanies
        $this->deleteFiles($ids, TendersCompanies::class);
        //borra los remarks de tendersCompanies
        $this->deleteRemarks($ids, TendersCompanies::class);
        //borra las notificaciones de las compañias licitantes
        $this->deleteNotifications($ids, TendersCompanies::class);

        //borra todas las compañias licitantes
        TendersCompanies::whereIn('id', $ids)
            ->delete();
    }

    public function deleteAllTenderVersions($ids)
    {
        //borra los archivos de las versiones de la licitación
        $this->deleteFiles($ids, TendersVersions::class);
        //borra las etiquetas de las versiones de la licitación
        $this->deleteTags($ids, TendersVersions::class);
        //borra las notificaciones de las versiones de la licitación
        $this->deleteNotifications($ids, TendersVersions::class);

        //borra todos las versiones de la licitacion
        TendersVersions::whereIn('id', $ids)
            ->delete();
    }

    public function deleteCategoryTenders($id)
    {
        CategoryTenders::where('tenders_id', $id)
            ->delete();
    }

    public function deleteTenderProponents($id)
    {
        Proponents::where('licitacion_id', $id)
            ->delete();
    }

    public function tenderTypeAll()
    {
        return [
            Tenders::TYPE_PUBLIC,
            Tenders::TYPE_PRIVATE
        ];
    }
}
