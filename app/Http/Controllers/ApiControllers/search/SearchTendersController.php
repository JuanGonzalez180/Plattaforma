<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\Tenders;
use App\Models\TendersVersions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchTendersController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }
 
    public function __invoke(Request $request)
    {
        $user = $this->validateUser();
        $companyID = $user->companyId();
        $name = $request->name;

        $tenders = Tenders::select('tenders.*')
                        ->where('tenders.company_id', $companyID)
                        ->where( function($query) use ($name){
                            $query->where(strtolower('name'),'LIKE','%'.strtolower($name).'%');
                        });
                        
        $tenders = $tenders->join('tenders_versions AS tversion', function($join) {
            $join->on('tenders.id', '=', 'tversion.tenders_id');
            $join->on('tversion.created_at', '=', DB::raw('(SELECT MAX(created_at) FROM tenders_versions WHERE tenders_id=tversion.tenders_id && tversion.status="'.TendersVersions::LICITACION_PUBLISH.'" )'));
        });

        $tenders = $tenders->orderBy('name', 'ASC')
                        ->get();

        return $this->showAllPaginate($tenders);
    }
}
