<?php

namespace App\Http\Controllers\ApiControllers\company;

use JWTAuth;
use App\Models\Company;
use App\Models\Products;
use Illuminate\Http\Request;
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

    public function detail(Request $request, $slug)
    {
        $user = $this->validateUser();

        $name = $request->name;

        $products = Products::select('products.*')
            ->where('products.status','=',Products::PRODUCT_PUBLISH)
            ->join('companies','companies.id','=','products.company_id')
            ->where('companies.slug','=',$slug)
            ->where(strtolower('products.name'),'LIKE','%'.strtolower($name).'%')
            ->orderBy('products.name', 'asc')
            ->get(); 

        if( !$products ){
            $productsError = [ 'products' => 'Error, no se ha encontrado ningun producto' ];
            return $this->errorResponse( $productsError, 500 );
        }

        return $this->showOneTransformNormal($products, 200);
    }
}
