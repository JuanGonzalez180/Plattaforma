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

    public function __invoke()
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $companyID = $user->companyId();
        if( $companyID && $user->userType() == 'demanda' ){
            // 
            // Filtros Búsquedas y demás
            $products = Products::all();

            foreach( $products as $key => $product ){
                $product->user['url'] = $product->user->image ? url( 'storage/' . $product->user->image->url ) : null;
                $product->company;
                $product->company->image;
            }
            return $this->showAllPaginate($products);
        }
        return [];
    }
}