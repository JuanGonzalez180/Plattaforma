<?php

namespace App\Http\Controllers\ApiControllers\password;

use App\Models\User;
use Illuminate\Http\Request;
use \Carbon\Carbon;
use App\Http\Controllers\ApiControllers\ApiController;

class CodeValidationController extends ApiController
{
    //
    public function store(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'code1' => 'required|numeric|min:0|max:9',
            'code2' => 'required|numeric|min:0|max:9',
            'code3' => 'required|numeric|min:0|max:9',
            'code4' => 'required|numeric|min:0|max:9',
            'code5' => 'required|numeric|min:0|max:9',
            'code6' => 'required|numeric|min:0|max:9',
        ];

        $this->validate( $request, $rules );

        $user = User::whereEmail($request->email)->first();

        if ($user){
            $codeConcatenado = $request->code1 . $request->code2 . $request->code3 . $request->code4 . $request->code5 . $request->code6;

            if( $user->code != $codeConcatenado ){
                $userError = [ 'code' => ['Error, no coinciden los códigos']];
                return $this->errorResponse( $userError, 500 );
            }

            if ( Carbon::now()->timestamp > Carbon::parse($user->code_time)->timestamp ){
                $userError = [ 'code' => ['Error, el código se ha vencido']];
                return $this->errorResponse( $userError, 500 );
            }

            return $this->showOneData( ['success' => 'El código es correcto', 'code' => $user->code ], 200);
        }

        $userError = [ 'email' => ['Error, no se ha encontrado un usuario con este email']];
        return $this->errorResponse( $userError, 500 );
    }
}
