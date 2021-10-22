<?php

namespace App\Http\Controllers\ApiControllers\publicity\advertisingplanspaidimages;

use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RegistrationPayments;
use App\Models\Advertisings;
use App\Models\AdvertisingPlansPaidImages;
use App\Models\AdvertisingPlansImages;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class AdvertisingPlansPaidImagesController extends ApiController
{
    public $routeFile = 'public/';
    public $routeAdvertisings = 'images/advertisings/';

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function edit($id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $advertisings = Advertisings::find($id);
        $advertisings->plan;
        $advertisings->status = $advertisings->status();
        $advertisings->plan->advertisingPlansImages;
        foreach ($advertisings->plan->advertisingPlansImages as $key => $image) {
            $image->imagesAdvertisingPlans;
        }
        $advertisings->advertisingPlansPaidImages;
        foreach ($advertisings->advertisingPlansPaidImages as $key => $image) {
            $image->image;
        }

        return $this->showOne($advertisings, 200);
    }

    public function update(Request $request, $id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $advertisings = Advertisings::find($id);

        if(!$advertisings->can_post_publicity()){
            $queryError = ['advertisings' => 'Error, El usuario no puede editar la publicidad, debe pagar o el estado debe estar sin iniciar'];
            return $this->errorResponse($queryError, 500);
        }

        foreach ($request->images as $key => $image) {
            if ($image["image"]) {

                $paidImage = $advertisings->advertisingPlansPaidImages->where('adver_plans_images_id', $image["id"]);

                if( $paidImage->count() ){
                    // $paidImage->first()->save();
                    $paidImage = $paidImage->first();
                } else {
                    $paidImage = $advertisings->advertisingPlansPaidImages()->create([
                        'adver_plans_images_id' => $image["id"]
                    ]);
                }

                $png_url    = "advertising-" . $paidImage->id . "-" . time() . ".jpg";
                $img        = $image["image"];
                $img        = substr($img, strpos($img, ",") + 1);
                $data       = base64_decode($img);

                $routeFile = $this->routeAdvertisings . $advertisings->id . '/' . $png_url;

                Storage::disk('local')->put($this->routeFile . $routeFile, $data);
                if ($paidImage->image) {
                    Storage::disk('local')->delete($this->routeFile . $paidImage->image->url);
                    $paidImage->image()->update(['url' => $routeFile]);
                } else {
                    Storage::disk('local')->put($this->routeFile . $routeFile, $data);
                    $paidImage->image()->create(['url' => $routeFile]);
                }
            }
        }



        return $request;
    }
}
