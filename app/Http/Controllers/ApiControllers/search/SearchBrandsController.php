<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\Brands;
use App\Http\Controllers\ApiControllers\ApiController;
use Illuminate\Http\Request;

class SearchBrandsController extends ApiController
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
        $user = $this->validateUser();
        $companyID = $user->companyId();
        $name = $request->name;

        $brandsCompany = Brands::where('status',Brands::BRAND_ENABLED)
            ->where('company_id','=',$companyID)
            ->where( function($query) use ($name){
                $query->where(strtolower('name'),'LIKE','%'.strtolower($name).'%');
            })
            ->orderBy('name', 'ASC')
            ->get(); 

        $brandsNotCompany = Brands::where('status',Brands::BRAND_ENABLED)
            ->where('company_id','<>',$companyID)
            ->where( function($query) use ($name){
                $query->where(strtolower('name'),'LIKE','%'.strtolower($name).'%');
            })
            ->orderBy('name', 'ASC')
            ->get(); 

        $merged = $brandsCompany->merge($brandsNotCompany);
        

        // return $this->showAllTransformer($merged);
        return $this->showAllPaginate($merged);
    }
}
