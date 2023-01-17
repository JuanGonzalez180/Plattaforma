<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesVersions;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Tags;
use App\Models\Files;
use App\Models\Quotes;
use App\Models\Company;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\QuotesVersions;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use App\Mail\SendUpdateQuoteCompany;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class QuotesVersionsController extends ApiController
{
    public $routeFile           = 'public/';
    public $routeQuoteVersion   = 'images/quote/';

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function store(Request $request) //crea nueva adenda de la cotizacion
    {
        $quote_id   = $request->quote_id;
        $files      = $request['files'];

        $lastVersion = QuotesVersions::where('quotes_id', '=', $quote_id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->first();

        $rules = [
            'adenda'    => 'required',
            'price'     => 'required|numeric',
            'project'   => 'required|numeric',
            'date'      => 'required',
            'hour'      => 'required'
        ];

        $project_date_end   = Carbon::parse(Quotes::find($quote_id)->project->date_end);
        $quote_date_end     = Carbon::parse(date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day'])));

        if ($quote_date_end->greaterThan($project_date_end)) {
            $quoteError = ['tender' => 'Error, La fecha de cierre de la cotización debe ser menor a la fecha de cierre del proyecto'];
            return $this->errorResponse($quoteError, 500);
        }

        DB::beginTransaction();

        $quoteVersionFields['adenda']  = $request['adenda'];
        $quoteVersionFields['price']   = $request['price'];

        if ($request['date']) {
            $quoteVersionFields['date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
        }

        if ($request['hour']) {
            $quoteVersionFields['hour'] = $this->timeFormat($request['hour']['hour']) . ':' . $this->timeFormat($request['hour']['minute']);
        }

        $quoteVersionFields['quotes_id']  = $quote_id;
        $quoteVersionFields['status']      = QuotesVersions::QUOTATION_PUBLISH;

        try {
            //*Crea la nueva adenda.
            $quotesVersions                = QuotesVersions::create($quoteVersionFields);
            //*Crea las etiquetas de la nueva adenda.
            foreach ($request->tags as $key => $tag) {
                $quotesVersions->tags()->create(['name' => $tag['displayValue']]);
            }
        } catch (\Throwable $th) {
            $errorTender = true;
            DB::rollBack();
            $quoteError = ['quoteVersion' => 'Error, no se ha podido crear la versión de la cotización'];
            return $this->errorResponse($quoteError, 500);
        }

        //* Si existe una nueva version copia los archivos de la adenda anterios a la actual
        if ($quotesVersions) {
            $id_old     = $lastVersion->id;
            $id_last    = $quotesVersions->id;

            if ($files) {
                foreach ($files as $key => $file) {
                    $oldVersion = Files::select('filesable_type', 'name', 'type', 'url')
                        ->where('id', $file['files_id'])
                        ->get()
                        ->first();

                    $fileName   = $oldVersion->name;
                    $newFolder  = $this->routeQuoteVersion . $id_last . '/documents';

                    $file_old_version = $oldVersion->url;
                    $file_new_version = $newFolder . '/' . $fileName;

                    Storage::copy($this->routeFile . $file_old_version, $this->routeFile . $file_new_version);

                    $quotesVersions->files()->create(['name' => $oldVersion->name, 'type' => $oldVersion->type, 'url' => $file_new_version]);            
                }
            }
        }
        // * Envia correos y notificaciones a las compañias participantes.
        $this->sendMessageQuoteVersion($quotesVersions->quotes->quotesCompaniesParticipating(), $quotesVersions->quotes);

        DB::commit();

        $this->sendRecommendQuote($quotesVersions->quotes);


        return $this->showOne($quotesVersions, 201);
    }

    public function sendRecommendQuote($quote)
    {
        $tags = $quote->quotesVersionLast()->tagsName();

        $companies = $quote->quoteCompaniesIds();

        $recommendToCompanies = ($quote->type == 'Publico') ? $this->getQueryCompaniesTags($tags, $companies) : [];

        if (sizeof($recommendToCompanies) > 0)
        {
            foreach ($recommendToCompanies as $key => $value) {
                $company = Company::find($value);
                $this->sendNotificationRecommendQuote($quote, $company->userIds());
                DB::table('temporal_recommendation')->insert([
                    'modelsable_id'     => $quote->id,
                    'modelsable_type'   => Quotes::class,
                    'company_id'        => $company->id,
                ]);
            }
        }
    }

    public function sendNotificationRecommendQuote($quote, $users)
    {
        $notifications = new Notifications();
        $notifications->registerNotificationQuery($quote, Notifications::NOTIFICATION_RECOMMEND_QUOTE, $users);
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

    public function sendMessageQuoteVersion($quotesCompanies, $tender)
    {
        $notifications = new Notifications();

        foreach ($quotesCompanies as $key => $quoteCompany) {
            if ($quoteCompany->status == QuotesCompanies::STATUS_PARTICIPATING) {
                //1. NOTIFICACIONES -> Envia las notificaciones a los usuarios por compañia participante
                $notifications->registerNotificationQuery(
                    $tender,
                    Notifications::NOTIFICATION_QUOTECOMPANYNEWVERSION,
                    $quoteCompany->quoteCompanyUsersIds()
                );
                // 2. CORREOS -> Envia los correos a los usuarios ya participantes
                $this->sendEmailQuoteVersion(
                    $quoteCompany->quoteCompanyEmails(),
                    $quoteCompany
                );
            }
        }
    }

    public function sendEmailQuoteVersion($UserEmails, $quoteCompany)
    {
        foreach ($UserEmails as $mail) {
            Mail::to(trim($mail))->send(new SendUpdateQuoteCompany(
                $quoteCompany->quote->name,
                $quoteCompany->quote->quotesVersionLast()->adenda,
                $quoteCompany->company->name
            ));
        }
    }

    public function timeFormat($value)
    {
        return (strlen((string)$value) <= 1) ? str_pad($value, 2, "0", STR_PAD_LEFT) : $value;
    }

    public function edit($id)
    {
        $lastVersion = QuotesVersions::where('quotes_id', '=', $id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->first();

        $lastVersion->id;
        $lastVersion->adenda;
        $lastVersion->price;
        $lastVersion->status;
        $lastVersion->date;
        $lastVersion->hour;

        return $this->showOne($lastVersion, 201);
    }

    public function update(Request $request, $id)
    {
        $lastVersion = QuotesVersions::where('quotes_id', '=', $id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->first();

        $rules = [
            'adenda'    => 'required',
            'price'     => 'required|numeric',
            'date'      => 'required',
            'hour'      => 'required'
        ];

        // Iniciar Transacción
        DB::beginTransaction();

        $lastVersion->adenda  = $request['adenda'];
        $lastVersion->price   = $request['price'];

        if ($request['date']) {
            $lastVersion->date = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
        }

        if ($request['hour']) {
            $lastVersion->hour = $this->timeFormat($request['hour']['hour']) . ':' . $this->timeFormat($request['hour']['minute']);
        }

        $lastVersion->status      = QuotesVersions::QUOTATION_PUBLISH;

        try {
            $lastVersion->save();

            // Tags
            // Eliminar los anteriores
            foreach ($lastVersion->tags as $key => $tag) {
                $tag->delete();
            }

            foreach ($request->tags as $key => $tag) {
                $lastVersion->tags()->create(['name' => $tag['displayValue']]);
            }

            // Axtualiza TenderVersion
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorTender = true;
            DB::rollBack();
            $quoteError = ['quoteVersion' => 'Error, no se ha podido actulizar la versión de la cotización.'];
            return $this->errorResponse($quoteError, 500);
        }

        DB::commit();
        $lastVersion->tags;
        return $this->showOne($lastVersion, 201);
    }
}
