<?php

namespace App\Http\Controllers\ApiControllers\querywall;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Quotes;
use App\Models\QueryWall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class quotesQueryAnswerController extends ApiController
{

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $quote_id = $request->quote_id;
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        if ($user->userType() != 'demanda') {
            $queryError = ['querywall' => 'Error, El usuario no puede listar preguntas'];
            return $this->errorResponse($queryError, 500);
        }

        $queryWallsNotAnswered  = QueryWall::where('querysable_id', $quote_id)
            ->where('status', QueryWall::QUERYWALL_ANSWERED)
            ->where('type', '=', 'Consulta')
            ->where('querysable_type', Quotes::class)
            ->orderBy('created_at', 'desc')
            ->get();

        $queryWallsAnswered  = QueryWall::where('querysable_id', $quote_id)
            ->where('status', '<>', QueryWall::QUERYWALL_ANSWERED)
            ->where('type', '=', 'Consulta')
            ->where('querysable_type', Quotes::class)
            ->orderBy('updated_at', 'desc')
            ->get();

        $queryWalls = $queryWallsAnswered
            ->merge($queryWallsNotAnswered);

        return $this->showAllPaginate($queryWalls);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
        $user = $this->validateUser();

        if ($user->userType() != 'demanda') {
            $queryError = ['querywall' => 'Error, El usuario no puede responder preguntas'];
            return $this->errorResponse($queryError, 500);
        }

        $queryAnswer = QueryWall::where('id', $id)
            ->where('querysable_type', Quotes::class)
            ->first();

        if (!$queryAnswer) {
            $queryError = ['querywall' => 'Error, La pregunta no exite en el muro de consultas de licitaciones'];
            return $this->errorResponse($queryError, 500);
        }

        $admin_company  = ($queryAnswer->company->user_id == $user->id) ? True : False;
        $tender_resp    = ($queryAnswer->queryWallQuoteUser() == $user->id) ? True : False;
        $project_resp   = ($queryAnswer->queryWallQuoteProjectUser() == $user->id) ? True : False;

        // if( $admin_company || $tender_resp || $project_resp ) {

        DB::beginTransaction();

        if ($request['visible']) {
            $queryFields['visible'] = $request['visible'];
        } else {
            $queryFields['answer']          = $request['answer'];
            $queryFields['date_answer']     = Carbon::now();
            $queryFields['status']          = QueryWall::QUERYWALL_ANSWERED;
            $queryFields['user_answer_id']  = $user->id;
        }
        try {
            $queryAnswer->update($queryFields);
        } catch (\Throwable $th) {
            DB::rollBack();
            $questionError = ['question' => 'Error, no se ha podido responder a la pregunta'];
            return $this->errorResponse($questionError, 500);
        }
        DB::commit();

        return $this->showOne($queryAnswer, 200);
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