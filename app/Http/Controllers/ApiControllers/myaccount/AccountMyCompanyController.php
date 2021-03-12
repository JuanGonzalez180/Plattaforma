<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use JWTAuth;
use App\Models\User;
use App\Models\Image;
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
                try{
                    $company = $user->company[0];
                    $company->image;
                    $imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
                    $company->imageCoverPage = $imageCoverPage;
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
        //
        $user = $this->validateUser();
        if( $user ){
            $rules = [
                'name' => ['required', 'regex:/^[a-zA-Z\s]*$/'],
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
            $company->map_lat = $request->latitud;
            $company->map_lng = $request->longitud;

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

            if( $request->imageCoverPage ){
                $png_url = "company-coverpage-".time().".jpg";
                $img = $request->imageCoverPage;
                $img = substr($img, strpos($img, ",")+1);
                $data = base64_decode($img);
                
                $routeFile = 'images/company/'.$company->id.'/'.$png_url;
                Storage::disk('local')->put( $this->routeFile . $routeFile, $data);

                // $imageCoverPage = Image::create(['url' => $routeFile, 'imageable_id' => $company->id, 'imageable_type' => 'App\Models\Company\CoverPage']);
                $imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
                if( !$imageCoverPage ){
                    $imageCoverPage = Image::create(['url' => $routeFile, 'imageable_id' => $company->id, 'imageable_type' => 'App\Models\Company\CoverPage']);
                }else{
                    Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->update(['url' => $routeFile]);
                    Storage::disk('local')->delete( $this->routeFile . $imageCoverPage->url );
                }
            }

            // Guardar
            $company->save();
            // ReSearch User
            $companyNew = Company::findOrFail($company->id);
            $companyNew->imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
            $companyNew->image;

            return $this->showOne($companyNew,200);
        }

        $error =  [ 'user' => ['Ha ocurrido un error al obtener el usuario']];
        return $this->errorResponse( $error, 500 );
    }
}
