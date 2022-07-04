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
    public function index()
    {
        //
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
            $errorTender = true;
            DB::rollBack();
            $quotationError = ['quotation' => 'Error, no se ha podido crear la cotización'];
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
        return (strlen((string)$value) == 1) ? str_pad($value,2,"0", STR_PAD_LEFT) : $value; 
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
        //
    }
}
