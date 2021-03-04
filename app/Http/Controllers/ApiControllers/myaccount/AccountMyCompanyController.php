<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class AccountMyCompanyController extends ApiController
{
    public $user = false;
    public $routeFile = 'public/';

    //
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            
        }

        return $this->user;
    }

    public function __invoke()
    {
        //
        $user = $this->validateUser();
        if( $user ){
            if( $user->company ){
                $user->company[0]->image;
                return $this->showOne($user->company[0],200);    
            }
            return $this->showOne($user,200);
        }

        $error =  [ 'user' => ['Ha ocurrido un error al obtener el usuario']];
        return $this->errorResponse( $error, 500 );
    }

    public function store(Request $request)
    {
        //
        $user = $this->validateUser();
        if( $user ){
            $rules = [
                'name' => 'required|alpha_num',
                'nit' => 'nullable|numeric',
                'country_code' => 'required',
                'web' => 'nullable|url',
                // 'country_backend' => 'required',
            ];
            $this->validate( $request, $rules );

            $company = $user->company[0];
            $company->name = $request->name;
            $company->nit = $request->nit;
            $company->country_code = $request->country_code;
            $company->web = $request->web;

            if( $request->image ){
                $png_url = "company-".time().".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",")+1);
                $data = base64_decode($img);
                
                $routeFile = 'images/company/'.$company->id.'/'.$png_url;
                Storage::disk('local')->put( $this->routeFile . $routeFile, $data);

                if( !$company->image ){
                    $company->image()->create(['url' => $routeFile]);
                }else{
                    Storage::disk('local')->delete( $this->routeFile . $company->image->url );
                    $company->image()->update(['url' => $routeFile]);
                }
            }

            // Guardar
            $company->save();
            // ReSearch User
            $companyNew = Company::findOrFail($company->id);
            $companyNew->image;

            return $this->showOne($companyNew,200);
        }

        $error =  [ 'user' => ['Ha ocurrido un error al obtener el usuario']];
        return $this->errorResponse( $error, 500 );
    }
}
