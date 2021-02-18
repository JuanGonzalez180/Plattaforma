<?php

namespace App\Http\Controllers\ApiControllers\company;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Mail\CreatedAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiControllers\ApiController;
use App\Models\TypesEntity;
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
            // Country https://restcountries.eu/
            'country_code' => 'required',
            'country_backend' => 'required',
            'email' => 'required|email|unique:users',
            'name' => 'required|alpha_num',
            'nit' => 'nullable|numeric',
            'password' => 'required|min:6|confirmed',
            'terms' => 'required',
            'type_entity_id' => 'required',
            'web' => 'nullable|url'
        ];

        $this->validate( $request, $rules );

        // Traer los tipos registrados
        $type = TypesEntity::find( $request['type_entity_id'] );

        //Verificar que este registrado y adicionar otras validaciones
        $errors = [];
        if ( $type['type']['slug'] == 'demanda' ) {

            if ( !$request['nit'] )
                $errors['nit'] = 'El campo nit es obligatorio';

            if ( !$request['web'] )
                $errors['web'] = 'El campo web es obligatorio';

        }

        //Verificar si existen errores
        if ( !empty( $errors ) )
            return $this->errorResponse( $errors, 500 );

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
            if( $username && !$userExist ){
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
        // $userFields['validated'] = User::USER_NO_VALIDATED;
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

                // Ingresar País en una Compañía
                $company->countries()->attach($request['country_backend']);

                DB::commit();
            } catch (\Throwable $th) {
                // Si existe algún error al generar la compañía
                DB::rollBack();
                $companyError = [ 'company' => 'Error, no se ha podido crear la compañia' ];
                return $this->errorResponse( $companyError, 500 );
            }
        }
        
        // Generar el correo de Verificación.
        Mail::to($user->email)->send(new CreatedAccount( $company, $user, $type['type']['slug'] ));

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
