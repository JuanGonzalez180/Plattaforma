<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str as Str;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class AccountMyServicesController extends ApiController
{

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            
        }

        return $this->user;
    }

    public function index()
    {
        $user = $this->validateUser();

        if( $user ){
            if( $user->company ){
                try{
                    $company = $user->company[0];
                    $company->companyCategoryServices;
                    $company->tags;

                    return $this->showOne($company,200);
                } catch (\Throwable $th) {

                }
            }
            return $this->showOne($user,200);
        }

        $error =  [ 'company' => ['Ha ocurrido un error al obtener la compañia']];
        return $this->errorResponse( $error, 500 );
    }

    public function store(Request $request)
    {
        $user = $this->validateUser();
        if( $user ){

            $company = $user->company[0];
            
            foreach( $company->tags as $key => $tag ){
                $tag->delete();
            }
            
            if( $request->tags ){
                foreach ($request->tags as $key => $tag) {
                    $company->tags()->create(['name' => $tag['displayValue']]);
                }
            }
            
            // Categorías Servicios
            // Eliminar los anteriores
            foreach( $company->companyCategoryServices as $key => $category ){
                $company->companyCategoryServices()->detach($category->id);
            }
            
            if( $request->categoriesServices ){
                foreach ($request->categoriesServices as $key => $categoryId) {
                    $company->companyCategoryServices()->attach($categoryId);
                }
            }

            // ReSearch User
            $companyNew = Company::findOrFail($company->id);

            return $this->showOne($companyNew,200);
        }

        $error =  [ 'user' => ['Ha ocurrido un error al obtener el usuario']];
        return $this->errorResponse( $error, 500 );
    }
}
