<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyQuotes;

use JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuotesCompanies;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyQuotesTransactController extends ApiController
{

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function postComparate(Request $request)
    {
        $user           = $this->validateUser();
        $companies_id   = $request->companies_id;

        $id_array = [];

        foreach($companies_id as $company_id){
            $id_array[] = $company_id['id'];
        }

        $quotesCompanies = QuotesCompanies::whereIn('id', $id_array)
            ->get();

        $transformer = QuotesCompanies::TRANSFORMER_QUOTE_COMPANY_SELECTED;

        return $this->showAllPaginateSetTransformer($quotesCompanies, $transformer);
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
