<?php

namespace App\Http\Controllers\ApiControllers\chat;

use JWTAuth;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class ChatController extends ApiController
{
    public function validateUser(){
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
    public function index()
    {
        //
        $user = $this->validateUser();

        $chats = Chat::where('user_id', $user->id)
                       ->orWhere('user_id_receive', $user->id)
                       ->orWhere('company_id', $user->companyId())
                       ->orWhere('company_id_receive', $user->companyId());

        return $this->showAllPaginate($chats);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Revisar si ya existe el CHAT.
        $user = $this->validateUser();

        $rules = [
            'id' => 'required',
            'type' => 'required',
            'company_id_receive' => 'required'
        ];

        $this->validate( $request, $rules );
        
        $chatFields['chatsable_id'] = $request->id;
        if( $request->type == 'tenders' ){
            $chatFields['chatsable_type'] = Tenders::class;
            if($user->userType() == 'demanda'){
                $companySend = $request->company;
                $companyReceive = $user->companyId();
            }elseif($user->userType() == 'oferta'){
                $companySend = $user->companyId();
                $companyReceive = $request->company;
            }
        }
        $chatFields['user_id'] = $user->id;
        $chatFields['company_id'] = $companySend;
        $chatFields['company_id_receive'] = $companyReceive;

        $chat = Chat::where('chatsable_id', $chatFields['chatsable_id'])
                    ->where('chatsable_type', $chatFields['chatsable_type'])
                    ->where('company_id', $chatFields['company_id'])
                    ->where('company_id_receive', $chatFields['company_id_receive']);
        
        if( !$chat ){
            // Iniciar Transacción
            DB::beginTransaction();
            try{
                // Crear Usuario
                $chat = Chat::create( $chatFields );
            } catch (\Throwable $th) {
                // Si existe algún error al momento de crear el usuario
                $errorUser = true;
                DB::rollBack();
                $userError = [ 'user' => 'Error, no se ha podido crear el chat entre las compañias' ];
                return $this->errorResponse( $userError, 500 );
            }
        }

        return $this->showOne($chat,201);
    }
}
