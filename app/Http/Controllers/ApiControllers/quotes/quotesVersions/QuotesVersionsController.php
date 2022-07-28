<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesVersions;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Files;
use App\Models\Quotes;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\QuotesVersions;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
// use App\Mail\SendUpdateTenderCompany;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class QuotesVersionsController extends ApiController
{
    public $routeFile           = 'public/';
    public $routeQuoteVersion   = 'images/quotes/';

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

                    // * Envia correos y notificaciones a las compañias participantes.
                    // $this->sendMessageTenderVersión($quotesVersions->quotes->quotesCompaniesParticipating(), $quotesVersions->quotes);


                }
            }
        }

        DB::commit();
        return $this->showOne($quotesVersions, 201);
    }

    public function sendMessageTenderVersión($tenderCompanies, $tender)
    {
        $notifications = new Notifications();

        // foreach ($tenderCompanies as $key => $tenderCompany) {
        //     if ($tenderCompany->status == TendersCompanies::STATUS_PARTICIPATING) {
        //         //1. NOTIFICACIONES -> Envia las notificaciones a los usuarios por compañia participante
        //         $notifications->registerNotificationQuery(
        //             $tender,
        //             Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION,
        //             $tenderCompany->tenderCompanyUsersIds()
        //         );
        //         // 2. CORREOS -> Envia los correos a los usuarios ya participantes
        //         $this->sendEmailTenderVersion(
        //             $tenderCompany->tenderCompanyEmails(),
        //             $tenderCompany
        //         );
        //     }
        // }
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
