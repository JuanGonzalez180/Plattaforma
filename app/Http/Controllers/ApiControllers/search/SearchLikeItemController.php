<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchLikeItemController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index(Request $request)
    {
        $type_user    = $this->validateUser()->userType();

        $result = [];
        if( !isset( $request->type_consult ) && !isset( $request->search_item ) )
        {
            $result = ($type_user == 'demanda') ? $this->getAllProducts() : $this->getAllTenders();
        }
        else if( isset( $request->type_consult ) && isset( $request->search_item ) )
        {
            if($type_user == 'Demanda')
            {

            }
            else if($type_user == 'Oferta')
            {

            }
        }

        return $result;
    }
}
