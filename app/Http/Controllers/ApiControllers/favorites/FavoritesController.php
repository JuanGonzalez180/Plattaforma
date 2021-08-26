<?php

namespace App\Http\Controllers\ApiControllers\favorites;

use JWTAuth;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Products;
use App\Models\Projects;
use App\Models\Interests;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class FavoritesController extends ApiController
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
        $user = $this->validateUser();

        $favorites = $user->interests
                              ->sortBy([ ['created_at', 'desc'] ]);
        foreach ($favorites as $key => $favorite) {
            $favorite->query_id = $favorite->interestsable_id;
            $item = [];
            if( $favorite->interestsable_type == Products::class ){
                $favorite->type = 'product';
                $item = Products::find($favorite->interestsable_id);
                $favorite->image = $item->image;
                $favorite->name = $item->name;
            }elseif( $favorite->interestsable_type == Tenders::class ){
                $favorite->type = 'tender';
                $item = Tenders::find($favorite->interestsable_id);
                $favorite->name = $item->name;
            }elseif( $favorite->interestsable_type == Company::class ){
                $favorite->type = 'company';
                $item = Company::find($favorite->interestsable_id);
                $favorite->company = $item;
                $favorite->image = $item->image;
                $favorite->name = $item->name;
            }elseif( $favorite->interestsable_type == Projects::class ){
                $favorite->type = 'project';
                $item = Projects::find($favorite->interestsable_id);
                $favorite->image = $item->image;
                $favorite->name = $item->name;
            }

            if( $favorite->type && $favorite->type!='company' ){
                $favorite->company = $item->company;
            }
        }
        return $this->showAllPaginate($favorites);
    }

    public function store(Request $request)
    {
        
    }

    public function destroy($id){
        // $user = $this->validateUser();
        // $interests = Interests::find($id);
        // $interests->delete();

        // return $this->showOneData( ['success' => 'Se ha eliminado correctamente.', 'code' => 200 ], 200);
    }
}
