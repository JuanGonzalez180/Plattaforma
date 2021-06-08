<?php

namespace App\Http\Controllers\ApiControllers\company;

use JWTAuth;
use App\Models\Company;
use App\Models\Products;
use Illuminate\Http\Request;
use App\Transformers\ProductsTransformer;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyProductsController extends ApiController
{
    //
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index( $slug )
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $company = Company::where('slug', $slug)->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }

        // Traer Productos de la compañía
        $company->products = $company->products
                        ->where('status', Products::PRODUCT_PUBLISH)
                        ->sortBy([ ['updated_at', 'desc'] ]);
        
        return $this->showAllPaginate($company->products);
    }

    public function show( $slug, $id ) {

        $user = $this->validateUser();

        $product = Products::where('id', $id)
                        ->where('status',Products::PRODUCT_PUBLISH)
                        ->first();

        if( !$id || !$product ){
            $prodcutError = [ 'project' => 'Error, no se ha encontrado ningun producto' ];
            return $this->errorResponse( $prodcutError, 500 );
        }

        $productTransform = new ProductsTransformer();

        return $this->showOneData( $productTransform->transformDetail($product), 200 );
    }
    
}
