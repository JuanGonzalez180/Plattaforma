<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\Company;
use App\Http\Controllers\ApiControllers\ApiController;
use Illuminate\Http\Request;

class SearchCompanyController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }
 
    public function __invoke(Request $request)
    {
        $name = $request->name;

        $companies = Company::select('companies.id','companies.name','companies.slug')
            ->where('companies.status','=',Company::COMPANY_APPROVED)
            ->join('types_entities','types_entities.id','=','companies.type_entity_id')
            ->join('types','types.id','=','types_entities.type_id')
            ->where('types.name','=','oferta')
            ->where( function($query) use ($name){
                $query->where(strtolower('companies.name'),'LIKE',strtolower($name).'%')
                ->orWhere(strtolower('companies.name'),'LIKE','% '.strtolower($name).'%');
            })->get();   
            
        return $this->showAllPaginate($companies);
    }
}