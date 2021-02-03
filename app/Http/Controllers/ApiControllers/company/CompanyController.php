<?php

namespace App\Http\Controllers\ApiControllers\company;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
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
            'country_id' => 'required',
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'nit' => 'required',
            'password' => 'required|min:6|confirmed',
            'terms' => 'required',
            'type_entity_id' => 'required',
            'web' => 'required'
        ];

        $this->validate( $request, $rules );

        $generator = new Generator();
        $userFields['name'] = $generator->generate($request['name']);
        $userFields['email'] = strtolower($request['email']);
        $userFields['password'] = bcrypt( $request->password );
        $userFields['verified'] = User::USER_NO_VERIFIED;
        $userFields['verification_token'] = User::generateVerificationToken();
        $userFields['admin'] = User::USER_REGULAR;

        $user = User::create( $userFields );

        $companyFields = [
            'name' => $request['name'],
            'type_entity_id' => $request['type_entity_id'],
            'nit' => $request['nit'],
            'country_id' => $request['country_id'],
            'web' => $request['web'],
            'user_id' => $user['id']
        ];
        $company = Company::create( $companyFields );
        // Si da error.
        // $user->delete($user->id);

        // 
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
