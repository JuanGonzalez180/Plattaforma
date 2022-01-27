<?php

namespace App\Http\Controllers\ApiControllers\messages;

use JWTAuth;
use App\Models\Chat;
use App\Models\User;
use App\Models\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class MessagesController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index(Request $request){
        $user = $this->validateUser();

        $rules = [
            'chat_id' => 'required'
        ];
        $this->validate( $request, $rules );

        $messages = Messages::where( 'chat_id', $request->chat_id )
                            ->orderBy('created_at', 'desc')
                            ->get();
                            
        foreach( $messages as $key => $message ){
            if( $user->id != $message->user_id ){
                $message->viewed = 1;
                $message->save();
            }
            $message->type = ( $message->user_id == $user->id ) ? 'me' : 'you';
        }

        return $this->showAllPaginate($messages);
    }

    public function store(Request $request){
        $user = $this->validateUser();

        $rules = [
            'chat_id' => 'required',
            'message' => 'required',
        ];
        $this->validate( $request, $rules );

        $messageFields['chat_id'] = $request->chat_id;
        $messageFields['user_id'] = $user->id;
        $messageFields['message'] = $request->message;

        DB::beginTransaction();
        try{
            // Crear Usuario
            $message = Messages::create( $messageFields );
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorUser = true;
            DB::rollBack();
            $userError = [ 'message' => 'Error, no se ha podido guardar el mensaje' ];
            return $this->errorResponse( $userError, 500 );
        }
        DB::commit();

        return $this->showOne($message,201);
    }
}
