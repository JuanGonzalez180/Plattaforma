<?php

namespace App\Http\Controllers\ApiControllers\quotes;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Quotes;
use App\Models\QuotesCompanies;
use App\Http\Controllers\Controller;
use App\Models\QuotesVersions;
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

class QuotesController extends ApiController
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
                $quotes = Quotes::select('quotes.id', 'quotes.name', 'quotes.type', 'quotes.description', 'quotes.project_id', 'quotes.company_id', 'quotes.user_id', 'quotes.date_update', 'quotes.created_at', 'quotes.updated_at')
                    ->where('quotes.company_id', $companyID);
            } else {
                $quotes = Quotes::select('quotes.id', 'quotes.name', 'quotes.type', 'quotes.description', 'quotes.project_id', 'quotes.company_id', 'quotes.user_id', 'quotes.date_update', 'quotes.created_at', 'quotes.updated_at')
                    ->where('quotes.user_id', $user->id)
                    ->where('quotes.company_id', $companyID);
            }

            if ($request->project) {
                $quotes = $quotes->where('project_id', $request->project);
            }

            // Filtrar por orden desc or asc
            if ($request->orderby && $request->order) {
                $quotes = $quotes->join('quotes_versions AS qversion', function ($join) {
                    $join->on('quotes.id', '=', 'qversion.quotes_id');
                    $join->on('qversion.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM quotes_versions WHERE quotes_id=qversion.quotes_id)'));
                });
                if ($request->orderby == 'date') {
                    $quotes = $quotes->orderBy('qversion.date', $request->order);
                } elseif ($request->orderby == 'price') {
                    $quotes = $quotes->orderBy('qversion.price', $request->order);
                }
            } else {
                $quotes = $quotes->orderBy('quotes.updated_at', 'desc');
            }

            if ($request->filter) {
                $quotes = $quotes->join('projects', function ($join) use ($request) {
                    $join->on('quotes.project_id', '=', 'projects.id')
                        ->where(strtolower('quotes.name'), 'LIKE', '%' . strtolower($request->filter) . '%')
                        ->orWhere(strtolower('projects.name'), 'LIKE', '%' . strtolower($request->filter) . '%');
                });
            }

            $quotes = $quotes->groupBy('quotes.id', 'quotes.name', 'quotes.type', 'quotes.description', 'quotes.project_id', 'quotes.company_id', 'quotes.user_id', 'quotes.date_update', 'quotes.created_at', 'quotes.updated_at')
                ->get();

            return $this->showAllPaginate($quotes);
        }

        return [];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'name'              => 'required',
            'description'       => 'required',
            'price'             => 'required|numeric',
            'project'           => 'required|numeric',
            'date'              => 'required',
            'hour'              => 'required',
            'quotation_type'    => 'required'
        ];

        $this->validate($request, $rules);

        $project_date_end   = Carbon::parse(Projects::find($request['project'])->date_end);
        $quote_date_end     = Carbon::parse(date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day'])));

        if ($quote_date_end->greaterThan($project_date_end)) {
            $quoteError = ['quote' => 'Error, La fecha de cierre de la cotización debe ser menor a la fecha de cierre del proyecto'];
            return $this->errorResponse($quoteError, 500);
        }

        // Iniciar Transacción
        DB::beginTransaction();


        // Datos
        $quotesFields['name']          = $request['name'];
        $quotesFields['description']   = $request['description'];
        $quotesFields['user_id']       = $request['user'] ?? $user->id;
        $quotesFields['company_id']    = $user->companyId();
        $quotesFields['project_id']    = $request['project'];
        $quotesFields['type']          = $request['quotation_type'];
        // El campo Adenda quedará igual al nombre de la cotización por primera vez.
        $quotesVersionFields['adenda'] = $request['name'];
        $quotesVersionFields['price'] = $request['price'];
        if ($request['date']) {
            $quotesVersionFields['date'] = date("Y-m-d", strtotime($request['date']['year'] . '-' . $request['date']['month'] . '-' . $request['date']['day']));
        }
        if ($request['hour']) {
            $quotesVersionFields['hour'] = $this->timeFormat($request['hour']['hour']) . ':' . $this->timeFormat($request['hour']['minute']);
        }

        try {
            // Crear Quotes/cotización
            $quotation = Quotes::create($quotesFields);

            $quotesVersionFields['quotes_id'] = $quotation->id;
            $quotesVersions = QuotesVersions::create($quotesVersionFields);
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorQuote = true;
            DB::rollBack();
            $quotationError = ['quotation' => $th];
            return $this->errorResponse($quotationError, 500);
        }

        if ($quotation) {
            if ($request->categories) {
                foreach ($request->categories as $key => $categoryId) {
                    $quotation->quoteCategories()->attach($categoryId);
                }
            }

            foreach ($request->tags as $key => $tag) {
                $quotesVersions->tags()->create(['name' => $tag['displayValue']]);
            }

            $quotation->quotesVersion = $quotesVersions;
        }
        DB::commit();

        return $this->showOne($quotation, 201);
    }

    public function timeFormat($value)
    {
        return (strlen((string)$value) == 1) ? str_pad($value, 2, "0", STR_PAD_LEFT) : $value;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $user = $this->validateUser();

        $quotes = Quotes::findOrFail($id);
        // $quotes->categories;
        $quotes->quotesVersion;
        $quotes->quoteCompanies;

        $quotes->companies = $this->companies($quotes->id);

        if ($quotes->quotesVersion) {
            foreach ($quotes->quotesVersion as $key => $quotesVersion) {
                $quotesVersion->tags;
                $quotesVersion->files;
            }
        }
        $quotes->quotesVersionLast = $quotes->quotesVersionLast();

        return $this->showOne($quotes, 201);
    }

    public function companies($id)
    {
        return QuotesCompanies::select('companies.id', 'companies.name', 'images.url')
            ->join('companies', 'companies.id', '=', 'quotes_companies.company_id')
            ->leftJoin('images', function ($join) {
                $join->on('images.imageable_id', '=', 'companies.id');
                $join->where('images.imageable_type', '=', Company::class);
            })
            ->where('quotes_companies.quotes_id', $id)
            ->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $user = $this->validateUser();

        $quotes = Quotes::findOrFail($id);
        $quotes->user;
        $quotes->categories;
        $quotes->version_last = $quotes->quotesVersionLast();
        $quotes->version_last->tags;
        $quotes->version_last->files;

        return $this->showOne($quotes, 201);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $quote = Quotes::find($id);

        if(!$quote) {
            return $this->errorResponse('La cotización no existe o ha sido eliminada.', 500);
        }

        DB::beginTransaction();
        $errorQuote = false;

        // try {
            $quoteStatus   = $quote->quotesVersionLast()->status;
            //-----1. borra todos los registros de compañias licitantes
            $quoteCompanies             = $quote->quoteCompanies->pluck('id');
            $companiesParticipate       = $this->getCompaniesParticipating($quoteCompanies);
            $this->deleteAllQuoteCompanies($quoteCompanies);
            //-----2. Borra todas las versiones de la cotización-----
            $quoteVersions     = $quote->quotesVersion->pluck('id');
            $this->deleteAllQuoteVersions($quoteVersions);
            //-----3. Borra los proponentes-----
            // $this->deleteQuoteProponents($quote->id);
            // -----4.borrar las invitaciones de cotizaciones a compañias-----
            // pendiente
            // -----5.borrar los datos de la cotización-----
            $this->deleteAllQuote($quote->id);
            $quote->delete();
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     $errorQuote = true;
        //     $quoteError = ['user' => 'Error, no se ha podido borrar la cotización'];
        //     return $this->errorResponse($quoteError, 500);
        // }

        if (!$errorQuote) {
            DB::commit();
            // $this->sendDeleteTenderCompanyEmail($quote->name, $companiesParticipate);
        }

        return $this->showOne($quote, 200);
    }

    public function deleteAllQuote($quote_id)
    {
        $this->deleteInterests([$quote_id], Quotes::class);
        //Borra los remarks de cotización
        $this->deleteRemarks([$quote_id], Quotes::class);
        //borra las notificaciones de cotización
        $this->deleteNotifications([$quote_id], Quotes::class);
        //borra las querywalls de cotización
        $this->deleteQueryWall([$quote_id], Quotes::class);
    }

    public function deleteeQuoteProponents($id)
    {
        Proponents::where('licitacion_id', $id)
            ->delete();
    }

    public function deleteAllQuoteVersions($ids)
    {
        //borra los archivos de las versiones de la cotización
        $this->deleteFiles($ids, QuotesVersions::class);
        //borra las etiquetas de las versiones de la cotización
        $this->deleteTags($ids, QuotesVersions::class);
        //borra las notificaciones de las versiones de la cotización
        $this->deleteNotifications($ids, QuotesVersions::class);

        //borra todos las versiones de la cotización
        QuotesVersions::whereIn('id', $ids)
            ->delete();
    }

    public function getCompaniesParticipating($quoteCompanies)
    {
        $quotesCompanies =  QuotesCompanies::whereIn('id', $quoteCompanies)
            ->where('status', QuotesCompanies::STATUS_PARTICIPATING)
            ->get();

        $companies = [];

        foreach ($quotesCompanies as $key => $value) {
            $companies[$key]['company']             = $value->company->name;
            $companies[$key]['email_responsible']   = $value->user->email;
            $companies[$key]['email_admin']         = $value->company->user->email;
        }

        return $companies;
    }

    public function deleteAllQuoteCompanies($ids)
    {
        //borra los archivos de quotesCompanies
        $this->deleteFiles($ids, QuotesCompanies::class);
        //borra los remarks de quotesCompanies
        $this->deleteRemarks($ids, QuotesCompanies::class);
        //borra las notificaciones de las compañias licitantes
        $this->deleteNotifications($ids, QuotesCompanies::class);

        //borra todas las compañias licitantes
        QuotesCompanies::whereIn('id', $ids)
            ->delete();
    }
}
