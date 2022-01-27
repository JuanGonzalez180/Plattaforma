<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\ApiControllers\ApiController;

class AccountChangePasswordController extends ApiController
{
    //
    public function store(Request $request)
    {
        $rules = [
            'passwordNow' => 'required',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate( $request, $rules );

        try {
            $user = JWTAuth::parseToken()->authenticate();
            $user->image;
            if( Hash::check($request->passwordNow, $user->password) ){
                // Change Password
                $user->forceFill([
                    'password' => Hash::make($request->password)
                ])->save();
                $user->setRememberToken(Str::random(60));

                return $this->showOneData( ['success' => 'Se ha cambiado la contraseña' ], 200);
            }else{
                $passwordError =  [ 'password' => ['Error, la contraseña actual es incorrecta']];
                return $this->errorResponse( $passwordError, 500 );
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

        }
        return $user;
    }
}
