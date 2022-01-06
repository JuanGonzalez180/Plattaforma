<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use JWTAuth;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use TaylorNetwork\UsernameGenerator\Generator;
use App\Mail\SendInvitation;

//use App\Http\Resources\TeamCollection;

use App\Http\Controllers\ApiControllers\ApiController;


class AccountMyTeamController extends ApiController
{
    //Validamos que el usuario tenga un TOKEN
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            
        }

        return $this->user;
    }

    // Generar Username y Validar que no exista en BD
    public function generateUsername(string $email) {
        $generator = new Generator();
        $userFields['username'] = false;
        $usernameCreated = $generator->usingEmail()->generate( $email );
        while( !$userFields['username'] ){
            $username = $generator->generate( $usernameCreated );
            $userExist = DB::table('users')->where('username', $username)->first();
            if( $username && !$userExist ){
                $userFields['username'] = $username;
            }else{
                $usernameCreated = $generator->generate( $usernameCreated . uniqid() );
            }
        }

        return $userFields['username'];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        if ( $user && count($user->company) && $user->company[0] ) {
            $companyID = $user->company[0]->id;
        } elseif ( $user && $user->team ) {
            $companyID = $user->team->company_id;
        }
        
        $teamCompany = Team::where('company_id', $companyID)
            // ->where('status',Team::TEAM_APPROVED)
            ->orderBy('id', 'desc')->get();
        // $teamCompany = Team::where('company_id', $companyID)->orderBy('id', 'desc')->paginate();
        

        foreach( $teamCompany as $key => $team ){
            // Registrar el usuario asociado en la respuesta
            $team->user;
            $team['url'] = $team->user->image ? url( 'storage/' . $team->user->image->url ) : null;

            if( !$team->user->name ) {
                $team->user['name'] = $team->user->email;
            }
        }

        return $this->showAllPaginate($teamCompany);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        if( !$user->isAdminFrontEnd() ){
            $userError = [ 'error' => ['Error, no tiene permisos para crear un integrante'] ];
            return $this->errorResponse( $userError, 500 );
        }

        $rules = [
            'email' => ['email', Rule::unique('users') ]
        ];
        
        $this->validate( $request, $rules );

        // Generar Username y Validar que no exista en BD
        $userFields['username'] = $this->generateUsername( $request['email'] );
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
            $userError = [ 'error' => ['Error, no se ha podido crear el usuario'] ];
            return $this->errorResponse( $userError, 500 );
        }
        
        if( !$errorUser ){
            try {
                $teamFields = [
                    'user_id' => $newUser->id,
                    'company_id' => $user->companyId()
                ];

                // Crear un miembro del equipo
                $team = Team::create( $teamFields );

                DB::commit();
            } catch (\Throwable $th) {
                // Si existe algún error al generar la compañía
                DB::rollBack();
                $teamError = [ 'error' => ['Error, no se ha podido crear el miembro del equipo'] ];
                return $this->errorResponse( $teamError, 500 );
            }
        }
        
        // Generar el correo de invitacion.
        Mail::to($newUser->email)->send(new SendInvitation( $newUser ));

        // Aquí debe devolver el usuario creado.
        return $this->showOne($newUser, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $idMember
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $idMember)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $rules = [
            'email' => ['email', Rule::unique('users') ]
        ];
        
        $dataMember = $this->validate( $request, $rules );
        $dataMember['username'] = $this->generateUsername( $request['email'] );

        // Buscamos el usuario si existe en la tabla "Team"
        $memberTeam = Team::findOrFail( $idMember );
        $userMemberTeam = $memberTeam->user;

        // Actualizamos email, username y pasamos nuevamente a estado Pendiente
        $userMemberTeam->update( $dataMember );
        $memberTeam->update([
            'status' => Team::TEAM_PENDING
        ]);

        // Generar el correo de invitacion.
        Mail::to($userMemberTeam->email)->send(new SendInvitation( $userMemberTeam ));

        // Aquí debe devolver el usuario editado.
        return $this->showOne($userMemberTeam, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $idMember
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $idMember)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        // Buscamos el usuario si existe en la tabla "Team"
        $memberTeam = Team::findOrFail( $idMember );
        $userMemberTeam = $memberTeam->user;
        
        if( $userMemberTeam->email !== $request['email'] ) {
            $userError = [ 'user' => ['Error, no se ha podido eliminar el integrante'] ];
            return $this->errorResponse( $userError, 500 );
        }

        // Eliminamos primero el integrante del equipo y luego su usuario registrado
        $memberTeam->delete();
        $userMemberTeam->delete();
        return $this->showOne($userMemberTeam, 200);
    }
}
