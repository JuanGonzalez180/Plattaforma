<?php

namespace App\Http\Controllers\ApiControllers\password;

use App\Models\User;
use App\Mail\SendCode;
use \Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiControllers\ApiController;

class SendCodeController extends ApiController
{
    //
    private function generateString($input, $strength = 16) {
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
    
        return $random_string;
    }

    public function store(Request $request)
    {
        $rules = [
            'email' => 'required|email',
        ];

        $this->validate( $request, $rules );

        $user = User::whereEmail($request->email)->first();
        if ($user){
            // Enviar Código
            $permitted_chars = '0123456789';
            $code = $this->generateString($permitted_chars, 6);

            // Guardar Código
            $user->code = $code;
            $minutes = 15;
            $user->code_time = Carbon::now()->addMinutes($minutes)->format('Y-m-d H:i:s');
            $user->save();

            Mail::to(trim($user->email))->send(new SendCode( $code, $minutes, $user ));
            return $this->showOneData( ['success' => 'Se ha enviado un correo electrónico'], 200);
        }

        $userError = [ 'email' => ['Error, no se ha encontrado un usuario con este email']];
        return $this->errorResponse( $userError, 500 );
    }
}
