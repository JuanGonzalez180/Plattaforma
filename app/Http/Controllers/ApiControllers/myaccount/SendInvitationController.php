<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use JWTAuth;
use App\Models\User;
use App\Models\Team;
use App\Mail\SendE;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use TaylorNetwork\UsernameGenerator\Generator;
use App\Http\Controllers\ApiControllers\ApiController;
use App\Mail\SendInvitation;

class SendInvitationController extends ApiController
{
    public function store(Request $request)
    {
        
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            
        }

        $rules = [
            'email' => ['email', Rule::unique('users') ]
        ];
        
        $this->validate( $request, $rules );

        // Generar Username y Validar que no exista en BD
        // Armar username Parametro $userFields['username']
        $generator = new Generator();
        $userFields['username'] = false;
        $usernameCreated = $generator->usingEmail()->generate( $request['email'] );
        while( !$userFields['username'] ){
            $username = $generator->generate( $usernameCreated );
            $userExist = DB::table('users')->where('username', $username)->first();
            if( $username && !$userExist ){
                $userFields['username'] = $username;
            }else{
                $usernameCreated = $generator->generate( $usernameCreated . uniqid() );
            }
        }

        $userFields['email'] = strtolower($request['email']);
        $userFields['password'] = bcrypt( Str::random(6) );
        $userFields['verified'] = User::USER_NO_VERIFIED;
        $userFields['admin'] = User::USER_REGULAR;

        // Iniciar Transacción
        DB::beginTransaction();
        $errorUser = false;
        try{
            // Crear Usuario
            $newUser = User::create( $userFields );
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorUser = true;
            DB::rollBack();
            $userError = [ 'user' => 'Error, no se ha podido crear el usuario' ];
            return $this->errorResponse( $userError, 500 );
        }
        
        if( !$errorUser ){
            $teamFields = [
                'user_id' => $newUser->id,
                'company_id' => $user->company[0]->id
            ];
            
            try {
                // Crear un miembro del equipo
                $team = Team::create( $teamFields );

                DB::commit();
            } catch (\Throwable $th) {
                // Si existe algún error al generar la compañía
                DB::rollBack();
                $teamError = [ 'team' => 'Error, no se ha podido crear el miembro del equipo' ];
                return $this->errorResponse( $teamError, 500 );
            }
        }
        
        // Generar el correo de invitacion.
        Mail::to($newUser->email)->send(new SendInvitation( $newUser ));

        // Aquí debe devolver el usuario con el TOKEN.
        return $this->showOne($newUser,201);
    }
}
