<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use App\Models\Products;
use App\Models\MetaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchProductsController extends ApiController
{   
    public $routeFile = 'public/';
    public $routeProducts = 'images/products/';

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function __invoke(Request $request)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();
        $companyID = $user->companyId();
        $name = $request->name;

        if( $companyID ){
            // 
            // Filtros Búsquedas y demás
            $products = Products::where('status', Products::PRODUCT_PUBLISH)
                                        ->where('company_id', $companyID)
                                        ->where( function($query) use ($name){
                                            $query->where(strtolower('name'),'LIKE','%'.strtolower($name).'%');
                                        })
                                        ->orderBy('name', 'ASC')
                                        ->get();

            return $this->showAllPaginate($products);
        }
        return [];
    }
}