<?php

namespace App\Http\Controllers\ApiControllers\querywall;

use JWTAuth;
use App\Models\User;
use App\Models\Tenders;
use App\Models\QueryWall;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;
class tenderQueryAnswerController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function update(Request $request, $id)
    {
        $user = $this->validateUser();

        if($user->userType() != 'demanda'){
            $queryError = [ 'querywall' => 'Error, El usuario no puede responder preguntas' ];
            return $this->errorResponse( $queryError, 500 );
        }

        $queryAnswer = QueryWall::where('id', $id)
            ->where('querysable_type', Tenders::class)
            ->first();

        if(!$queryAnswer) {
            $queryError = [ 'querywall' => 'Error, La pregunta no exite en el muro de consultas de licitaciones' ];
            return $this->errorResponse( $queryError, 500 );
        }

        if( (!$request['visible']) && ($queryAnswer->status == QueryWall::QUERYWALL_ANSWERED) ) {
            $queryError = [ 'querywall' => 'Error, La pregunta del muro de consultas ha sido respondia' ];
            return $this->errorResponse( $queryError, 500 );
        }


        $admin_company  = ($queryAnswer->company->user_id == $user->id) ? True : False;
        $tender_resp    = ($queryAnswer->queryWallTenderUser() == $user->id) ? True : False;
        $project_resp   = ($queryAnswer->queryWallProjectUser() == $user->id) ? True : False;

        if( $admin_company || $tender_resp || $project_resp ) {

            DB::beginTransaction();

            if($request['visible']) {
                $queryFields['visible'] = $request['visible'];
            } else {
                $queryFields['answer'] = $request['answer'];
                $queryFields['status'] = QueryWall::QUERYWALL_ANSWERED;
            }

            try{
                $queryAnswer->update( $queryFields );
            }catch(\Throwable $th){
                DB::rollBack();
                $questionError = [ 'question' => 'Error, no se ha podido responder a la pregunta' ];
                return $this->errorResponse( $questionError, 500 );
            }
            DB::commit();

            return $this->showOne($queryAnswer,200);

        }else{
            $queryError = [ 'querywall' => 'Error, El usuario no tiene privilegios para responder preguntas del muro de consultas' ];
            return $this->errorResponse( $queryError, 500 );
        }

    }

    public function destroy( int $id)
    {
        $user = $this->validateUser();

        if($user->userType() != 'demanda'){
            $queryError = [ 'querywall' => 'Error, El usuario no puede responder preguntas' ];
            return $this->errorResponse( $queryError, 500 );
        }

        $queryAnswer = QueryWall::where('id', $id)
            ->where('querysable_type', Tenders::class)
            ->first();

        if(!$queryAnswer) {
            $queryError = [ 'querywall' => 'Error, La pregunta no exite en el muro de consultas de licitaciones' ];
            return $this->errorResponse( $queryError, 500 );
        }

        $admin_company  = ($queryAnswer->company->user_id == $user->id) ? True : False;
        $tender_resp    = ($queryAnswer->queryWallTenderUser() == $user->id) ? True : False;
        $project_resp   = ($queryAnswer->queryWallProjectUser() == $user->id) ? True : False;

        if( $admin_company || $tender_resp || $project_resp ) {
            $queryAnswer->delete();
        }else{
            $queryError = [ 'querywall' => 'Error, El usuario no tiene privilegios para barrar preguntas del muro de consultas' ];
            return $this->errorResponse( $queryError, 500 );
        }

        return $this->showOneData( ['success' => 'Se ha eliminado correctamente la pregunta del muro de consultas', 'code' => 200 ], 200);

    }

    public function index( Request $request )
    {
        $tender_id = $request->tender_id;

        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        if($user->userType() != 'demanda'){
            $queryError = [ 'querywall' => 'Error, El usuario no puede responder preguntas' ];
            return $this->errorResponse( $queryError, 500 );
        }

        $queryWallsNotAnswered  = QueryWall::where('querysable_id', $tender_id)
            ->where('status',QueryWall::QUERYWALL_ANSWERED)
            ->where('querysable_type', Tenders::class)
            ->orderBy('created_at', 'desc')
            ->get();   

        $queryWallsAnswered  = QueryWall::where('querysable_id', $tender_id)
            ->where('status','<>',QueryWall::QUERYWALL_ANSWERED)
            ->where('querysable_type', Tenders::class)
            ->orderBy('updated_at', 'desc')
            ->get();   

        $queryWalls = $queryWallsNotAnswered
            ->merge($queryWallsAnswered);

        return $this->showAllPaginate($queryWalls);
    }

}
