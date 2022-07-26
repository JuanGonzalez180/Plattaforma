<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesCompanies;

use JWTAuth;
use Illuminate\Http\Request;
use App\Models\QuotesCompanies;
use App\Models\QuotesVersions;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class QuotesCompaniesActionController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }


    public function SelectedWinner(Request $request)
    {
        $id                = $request->id;
        $quoteCompany      = QuotesCompanies::find($id);
        $quoteVersionLast  = $quoteCompany->quote->quotesVersionLast();


        DB::beginTransaction();

        $quoteCompany->winner      = QuotesCompanies::WINNER_TRUE;
        $quoteVersionLast->status  = QuotesVersions::QUOTATION_FINISHED;

        try {
            $quoteCompany->save();
            $quoteVersionLast->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $tenderError = ['tender' => 'Error, no se ha podido gestionar la solicitud de la licitación'];
            return $this->errorResponse($tenderError, 500);
        }
        //envia los correos a las empresas que participaron en la licitación
        // $this->sendEmailTenderCompanies($tenderCompany);
        //envia las notificaciones a las compañias licitantes. incluyendo a la compañia ganadora y a las demas que participarón
        // $this->sendNotificationTenderCompanies($tenderCompany);
        return $this->showOne($quoteCompany, 200);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
