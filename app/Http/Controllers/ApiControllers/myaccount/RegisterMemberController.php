<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\ApiControllers\ApiController;

class RegisterMemberController extends ApiController
{
    public function store(Request $request)
    {
        //Obtenemos el usuario de tipo "User"
        $newMember = User::where('username', $request->username)->first();

        if ( $newMember['team'] ) {
            //Obtenemos el integrante del equipo de tipo "Team"
            $dataTeam = $newMember->team;
            if ( $dataTeam->status == Team::TEAM_PENDING ) {
                $rules = [
                    'username' => ['alpha_dash','max:255', Rule::unique('users')->ignore($newMember->id)],
                    'name' => ['required', 'max:255'],
                    'lastname' => ['required', 'max:255'],
                    'position' => ['required', 'max:255'],
                    'phone' => ['required'],
                    'password' => ['required', 'min:6', 'confirmed'],
                    'terms' => 'required',
                ];
                
                $dataMember = $this->validate( $request, $rules );

                $dataMember['password'] = bcrypt( $dataMember['password'] );
                $dataMember['status'] = Team::TEAM_APPROVED;

                //Actualizamos los registros
                $newMember->update( $dataMember );
                $dataTeam->update( $dataMember );
            } else {
                $newMember["already_registered"] = true;
            }

            // $newMember->companyName = $newMember->company->name;


            return $this->showOne($newMember, 201);
        }

        $teamError = [ 'user' => 'Error, no eres un integrante del equipo' ];
        return $this->errorResponse( $teamError, 500 );
    }
}
