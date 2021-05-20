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
        $brands = Brands::where('status',Brands::BRAND_ENABLED)
            ->where(strtolower('name'),'LIKE',strtolower($request->name).'%')
            ->get(); 
            
        return $this->showAllPaginate($brands);
    }
}
