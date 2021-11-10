<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyChanges;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str as Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyChangesNameController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function store(Request $request){
        $user = $this->validateUser();

        $rules = [
            'name' => ['required', 'regex:/^[a-zA-Z\s]*$/']
        ];
        $this->validate( $request, $rules );

        $company = $user->company[0];
        $company->name = $request->name;

        // Guardar
        $company->slug = Str::slug($request->name);
        try{
            // Editar la compañía
            $company->save();
        } catch (\Throwable $th) {
            $error =  [ 'user' => ['El nombre de la compañía ya existe']];
            return $this->errorResponse( $error, 500 );
        }

        $companyNew = Company::findOrFail($company->id);
        return $this->showOne($companyNew,200);
    }
}