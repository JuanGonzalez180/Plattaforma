<?php

namespace App\Http\Controllers\ApiControllers\company;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Mail\CreatedAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiControllers\ApiController;
use TaylorNetwork\UsernameGenerator\Generator;

class CompanyController extends ApiController
{
    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $rules = [
            'country_code' => 'required',
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'nit' => 'required|numeric',
            'password' => 'required|min:6|confirmed',
            'terms' => 'required',
            'type_entity_id' => 'required',
            'web' => 'nullable|url'
        ];

        $this->validate( $request, $rules );

        // Generar Username y Validar que no exista en BD
        // Armar username Parametro $userFields['username']
        $generator = new Generator();
        $userFields['username'] = false;
        $usernameCreated = $request['name'];
        $i=0;
        while( !$userFields['username'] ){
            // 1ra vez
            $username = $generator->generate( $usernameCreated );
            $userExist = DB::table('users')->where('username', $username)->first();
            if( !$userExist ){
                $userFields['username'] = $username;
            }elseif($i==0){
                // 2ra vez
                $usernameCreated = $generator->usingEmail()->generate($request['email']);
            }else{
                $usernameCreated = $generator->generate( $request['name'].uniqid() );
            }
            $i++;
        }

        $userFields['email'] = strtolower($request['email']);
        $userFields['password'] = bcrypt( $request->password );
        $userFields['verified'] = User::USER_NO_VERIFIED;
        $userFields['validated'] = User::USER_NO_VALIDATED;
        $userFields['verification_token'] = User::generateVerificationToken();
        $userFields['admin'] = User::USER_REGULAR;

        // Iniciar Transacción
        DB::beginTransaction();
        $errorUser = false;
        try{
            // Crear Usuario
            $user = User::create( $userFields );
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorUser = true;
            DB::rollBack();
            $userError = [ 'user' => 'Error, no se ha podido crear el usuario' ];
            return $this->errorResponse( $userError, 500 );
        }
        
        if( !$errorUser ){
            $companyFields = [
                'name' => $request['name'],
                'type_entity_id' => $request['type_entity_id'],
                'nit' => $request['nit'],
                'country_code' => $request['country_code'],
                'web' => $request['web'],
                'user_id' => $user['id']
            ];
            
            try {
                // Crear la compañia
                $company = Company::create( $companyFields );
                DB::commit();
            } catch (\Throwable $th) {
                // Si existe algún error al generar la compañía
                DB::rollBack();
                $companyError = [ 'company' => 'Error, no se ha podido crear la compañia' ];
                return $this->errorResponse( $companyError, 500 );
            }
        }
        
        // Generar el correo de Verificación.
        Mail::to($user->email)->send(new CreatedAccount( $company, $user ));

        // Aquí debe devolver el usuario con el TOKEN.
        return $this->showOne($user,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
