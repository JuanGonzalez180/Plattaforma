<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use JWTAuth;
use App\Models\User;
use App\Models\Team;
use App\Http\Controllers\ApiControllers\ApiController;

class AccountMyTeamController extends ApiController
{
    public function __invoke()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            
        }

        if ( $user && count($user->company) && $user->company[0] ) {
            $companyID = $user->company[0]->id;
        } elseif ( $user && $user->team ) {
            $companyID = $user->team->company_id;
        }
        
        $teamCompany = Team::where('company_id', $companyID)->get();
        foreach( $teamCompany as $key => $team ){
            $team->user;
            // $team->user->image;
        }

        // Aquí debe devolver todos los integrantes del equipo
        return $this->showAll($teamCompany, 200);
    }
}
