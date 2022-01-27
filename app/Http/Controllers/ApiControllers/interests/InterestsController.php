<?php

namespace App\Http\Controllers\ApiControllers\interests;

use JWTAuth;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Products;
use App\Models\Projects;
use App\Models\Interests;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class InterestsController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function findInterests( $id, $user_id, $type ){
        $interests = Interests::where('interestsable_id', $id)
                                ->where('user_id', $user_id);

        if( $type === 'tenders' ){
            $interests = $interests->where('interestsable_type', Tenders::class);
        }elseif( $type === 'projects' ){
            $interests = $interests->where('interestsable_type', Projects::class);
        }elseif( $type === 'products' ){
            $interests = $interests->where('interestsable_type', Products::class);
        }elseif( $type === 'companies' ){
            $interests = $interests->where('interestsable_type', Company::class);
        }

        return $interests->first();
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();
        $favorite = $this->findInterests($request->id, $user->id, $request->type );

        if( $favorite ){
            return $this->showOne($favorite,200);
        }else{
            return [];
        }
    }

    public function store(Request $request)
    {
        //
        $user = $this->validateUser();
        if( $request->type && $user->id && $request->id ){
            if( $request->type === 'tenders' ){
                $item = Tenders::find($request->id);
            }elseif( $request->type === 'products' ){
                $item = Products::find($request->id);
            }elseif( $request->type === 'projects' ){
                $item = Projects::find($request->id);
            }elseif( $request->type === 'companies' ){
                $item = Company::find($request->id);
            }

            // Eliminar el anterior
            $favorite = $this->findInterests($request->id, $user->id, $request->type );
            if( $favorite ){
                $favorite->delete();
            }else{
                $item->interests()->create([ 'user_id' => $user->id ]);
                $favorite = $this->findInterests($request->id, $user->id, $request->type );
            }

            return $this->showOne($favorite,200);
        }else{
            $calificationError = [ 'calification' => 'Error, no se ha podido registrar el favorito' ];
            return $this->errorResponse( $calificationError, 500 );
        }

        return [];
    }

    public function destroy($id){
        $user = $this->validateUser();
        $interests = Interests::find($id);
        $interests->delete();

        return $this->showOneData( ['success' => 'Se ha eliminado correctamente.', 'code' => 200 ], 200);
    }
}
