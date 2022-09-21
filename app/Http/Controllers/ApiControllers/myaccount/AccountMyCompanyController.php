<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use JWTAuth;
use App\Models\User;
use App\Models\Image;
use App\Models\Company;
use App\Models\SocialNetworksRelation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str as Str;
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

    public function validateUser()
    {
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

        if ($user) {
            if ($user->company) {
                try {
                    $company = $user->company[0];
                    $company->image;
                    $company->address;
                    $company->socialnetworks;
                    // var_dump($company->socialnetworks);
                    $imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
                    $company->imageCoverPage = $imageCoverPage;
                    return $this->showOne($company, 200);
                } catch (\Throwable $th) {
                }
            }
            return $this->showOne($user, 200);
        }

        $error =  ['company' => ['Ha ocurrido un error al obtener la compañia']];
        return $this->errorResponse($error, 500);
    }

    public function store(Request $request)
    {
        //
        $user = $this->validateUser();
        if ($user) {
            $rules = [
                'name'          => 'required',
                'nit'           => 'nullable',
                'country_code'  => 'required',
                // 'web' => 'nullable|url',
                // 'country_backend' => 'required',
            ];
            $this->validate($request, $rules);

            $company = $user->company[0];
            $company->name = $request->name;
            $company->nit = $request->nit;
            $company->country_code = $request->country_code;
            $company->web = $request->web;
            $company->description = $request->description;

            if ($request->image) {
                $png_url = "company-" . time() . ".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",") + 1);
                $data = base64_decode($img);

                $routeFile = 'images/company/' . $company->id . '/' . $png_url;
                Storage::disk('local')->put($this->routeFile . $routeFile, $data);

                if (!$company->image) {
                    $company->image()->create(['url' => $routeFile]);
                } else {
                    Storage::disk('local')->delete($this->routeFile . $company->image->url);
                    $company->image()->update(['url' => $routeFile]);
                }
            }

            if ($request->imageCoverPage) {
                $png_url = "company-coverpage-" . time() . ".jpg";
                $img = $request->imageCoverPage;
                $img = substr($img, strpos($img, ",") + 1);
                $data = base64_decode($img);

                $routeFile = 'images/company/' . $company->id . '/' . $png_url;
                Storage::disk('local')->put($this->routeFile . $routeFile, $data);

                $imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
                if (!$imageCoverPage) {
                    $imageCoverPage = Image::create(['url' => $routeFile, 'imageable_id' => $company->id, 'imageable_type' => 'App\Models\Company\CoverPage']);
                } else {
                    Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->update(['url' => $routeFile]);
                    Storage::disk('local')->delete($this->routeFile . $imageCoverPage->url);
                }
            }

            if ($request->socialnetworks) {
                foreach ($request->socialnetworks as $key => $social) {
                    // var_dump( $social );
                    $socialNetwork = SocialNetworksRelation::where('socialable_id', $company->id)
                        ->where('socialable_type', Company::class)
                        ->where('social_networks_id', $social['id'])
                        ->first();

                    if ($social['link']) {
                        if (!$socialNetwork) {
                            $company->socialnetworks()->create(['link' => $social['link'], 'social_networks_id' => $social['id']]);
                        } else {
                            $socialNetwork->update(['link' =>  $social['link']]);
                        }
                    } elseif ($socialNetwork) {
                        $socialNetwork->delete();
                    }
                }
            }

            if ($request->address || $request->latitud || $request->longitud) {
                if (!$company->address) {
                    $company->address()->create([
                        // 'address' => $request->address,
                        // 'latitud' => $request->latitud,
                        // 'longitud' => $request->longitud
                        'address'   => '',
                        'latitud'   => '8.9814453',
                        'longitud'  => '-79.5188013'
                    ]);
                } else {
                    $company->address()->update([
                        // 'address'   => '',
                        // 'latitud'   => '8.9814453',
                        // 'longitud'  => '-79.5188013'
                        'address' => $request->address,
                        'latitud' => $request->latitud,
                        'longitud' => $request->longitud
                    ]);
                }
            }

            // Guardar
            $company->slug = Str::slug($request->name);
            try {
                // Editar la compañía
                $company->save();
            } catch (\Throwable $th) {
                $error =  ['user' => ['El nombre de la compañía ya existe']];
                return $this->errorResponse($error, 500);
            }

            // ReSearch User
            $companyNew = Company::findOrFail($company->id);
            $companyNew->imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
            $companyNew->image;
            $companyNew->address;
            $companyNew->socialnetworks;

            return $this->showOne($companyNew, 200);
        }

        $error =  ['user' => ['Ha ocurrido un error al obtener el usuario']];
        return $this->errorResponse($error, 500);
    }
}
