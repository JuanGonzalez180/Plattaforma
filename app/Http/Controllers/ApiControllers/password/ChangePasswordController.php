<?php

namespace App\Http\Controllers\ApiControllers\password;

use App\Models\User;
use App\Mail\SendCode;
use \Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiControllers\ApiController;

class ChangePasswordController extends ApiController
{
    //
    //
    public function store(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'code' => 'required|numeric',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate( $request, $rules );

        $user = User::whereEmail($request->email)->first();

        if ($user){
            if( $user->code != $request->code ){
                $userError = [ 'code' => ['Error, no coinciden los códigos']];
                return $this->errorResponse( $userError, 500 );
            }

            if ( Carbon::now()->timestamp > Carbon::parse($user->code_time)->timestamp ){
                $userError = [ 'code' => ['Error, el código se ha vencido']];
                return $this->errorResponse( $userError, 500 );
            }

            // Change Password
            $user->forceFill([
                'password' => Hash::make($request->password)
            ])->save();
            $user->setRememberToken(Str::random(60));

            return $this->showOneData( ['success' => 'Se ha cambiado la contraseña', 'code' => $user->code ], 200);
        }

        $userError = [ 'email' => ['Error, no se ha encontrado un usuario con este email']];
        return $this->errorResponse( $userError, 500 );
    }
}
