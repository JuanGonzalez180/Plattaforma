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
class tenderQueryQuestionController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function store(Request $request)
    {
        $user = $this->validateUser();
        $company_id = $user->companyId();

        if($user->userType() != 'oferta'){
            $queryError = [ 'querywall' => 'Error, El usuario no puede responder preguntas' ];
            return $this->errorResponse( $queryError, 500 );
        }

        $rules = [
            'question' => 'required|max:1000'
        ];
        
        $this->validate( $request, $rules );

        DB::beginTransaction();

        $questionFields = $request->all();
        $questionFields['querysable_id']    = $request->tender_id;
        $questionFields['querysable_type']  = Tenders::class;
        $questionFields['company_id']       = $company_id;
        $questionFields['user_id']          = $user->id;
        $questionFields['question']         = $request->question;
        $questionFields['status']           = QueryWall::QUERYWALL_PUBLISH;

        try{
            $question = QueryWall::create( $questionFields );
        }catch(\Throwable $th){
            $errorquestion = true;
            DB::rollBack();
            $questionError = [ 'question' => 'Error, no se ha podido crear la pregunta' ];
            return $this->errorResponse( $questionError, 500 );
        }
        DB::commit();
        return $this->showOne($question,201);   
    }


    public function update(Request $request, $id)
    {
        $user = $this->validateUser();

        if($user->userType() != 'oferta'){
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

        if( $queryAnswer->user_id == $user->id ) {

            DB::beginTransaction();
            $queryFields['question'] = $request['question'];

            try{
                $queryAnswer->update( $queryFields );
            }catch(\Throwable $th){
                DB::rollBack();
                $questionError = [ 'question' => 'Error, no se ha podido modificar la pregunta' ];
                return $this->errorResponse( $questionError, 500 );
            }
            DB::commit();

            return $this->showOne($queryAnswer,200);

        }else{
            $queryError = [ 'querywall' => 'Error, El usuario no tiene privilegios para modificar la pregunta del muro de consultas' ];
            return $this->errorResponse( $queryError, 500 );
        }

    }

    public function destroy( int $id)
    {
        $user = $this->validateUser();

        if($user->userType() != 'oferta'){
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

        if( $queryAnswer->user_id == $user->id ) {
            $queryAnswer->delete();
        }else{
            $queryError = [ 'querywall' => 'Error, El usuario no tiene privilegios para barrar la pregunta del muro de consultas' ];
            return $this->errorResponse( $queryError, 500 );
        }

        return $this->showOneData( ['success' => 'Se ha eliminado correctamente la pregunta del muro de consultas', 'code' => 200 ], 200);
    }

}
    

