<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersAction;

use JWTAuth;
use App\Models\Tenders;
use App\Models\QueryWall;
use Illuminate\Http\Request;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersActionController extends ApiController
{
    //
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function updateTenderUser(Request $request, $id)
    {
        $user = $this->validateUser();

        if($user->userType() != 'demanda'){
            $tenderError = [ 'querywall' => 'Error, El usuario no puede editar la licitación' ];
            return $this->errorResponse( $tenderError, 500 );
        }

        $tender = Tenders::find($id);

        //verifica si el usuario es el responsabel del proyecto
        $project_admin = ($tender->project->user_id == $user->id) ? True : False;

        //verifica si el usuario es el admnistrador de la compañia
        $company_admin = ($tender->company->user_id == $user->id) ? True : False;


        if($project_admin || $company_admin) {

            DB::beginTransaction();

            $tenderFields['user_id'] = $request['user_id'];

            try{
                $tender->update( $tenderFields );
            } catch (\Throwable $th) {
                // Si existe algún error al momento de editar el tender
                DB::rollBack();
                $tenderError = [ 'tender' => 'Error, no se ha podido editar la licitación'];
                return $this->errorResponse( $tenderError, 500 );
            }

            DB::commit();

        } else {
            $tenderError = [ 'querywall' => 'Error, El usuario no tiene privilegios para asignar un responsable a una licitación' ];
            return $this->errorResponse( $tenderError, 500 );
        }

        return $this->showOne($tender,200);

    }

}
