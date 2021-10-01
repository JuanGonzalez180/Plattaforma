<?php

namespace App\Http\Controllers\ApiControllers\chat;

use JWTAuth;
use App\Models\Chat;
use App\Models\User;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Transformers\ChatTransformer;
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

        $chats = Chat::where('chats.user_id', $user->id)
                       ->orWhere('user_id_receive', $user->id)
                       ->orWhere('company_id', $user->companyId())
                       ->orWhere('company_id_receive', $user->companyId())
                       ->leftJoin('messages as msg', function($q){
                            $q->on('msg.chat_id', '=', 'chats.id')
                                ->on('msg.updated_at', '=', DB::raw('(select max(updated_at) from messages where chat_id=msg.chat_id)'));
                       })
                       ->select('chats.*', 'msg.updated_at as updated_at_new')
                       ->orderBy('updated_at_new', 'desc')
                       ->get();
        
        return $this->showAllPaginate($chats);
    }

    public function notread()
    {
        $user = $this->validateUser();

        $chats = Chat::where('user_id', $user->id)
                       ->orWhere('user_id_receive', $user->id)
                       ->orWhere('company_id', $user->companyId())
                       ->orWhere('company_id_receive', $user->companyId())
                       ->pluck('id')
                       ->toArray();
        
        $countMessages = Messages::whereIn( 'chat_id', $chats )
                            ->where( 'viewed', 0 )
                            ->where( 'user_id', '<>', $user->id )
                            ->get()
                            ->count();
        
        return $this->showOneData( ['count' => $countMessages ], 200);
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
            'company_id' => 'required'
        ];

        $this->validate( $request, $rules );
        
        $chatFields['chatsable_id'] = $request->id;
        if( $request->type == 'tenders' ){
            $chatFields['chatsable_type'] = Tenders::class;
            $tender = Tenders::find($request->id);
            if( !$tender ){
                $tenderError = [ 'tender' => 'Error, no se ha encuentrado una licitación' ];
                return $this->errorResponse( $tenderError, 500 );
            }
            $companySend = $tender->company_id;
            
            if($user->userType() == 'demanda'){
                $company = Company::find($request->company_id);
                $companyReceive = $company->id;
                $userReceive = $company->user_id;
            }elseif($user->userType() == 'oferta'){
                $companyReceive = $user->companyId();
                $userReceive = $tender->user_id;
            }
        }
        $chatFields['user_id'] = $user->id;
        $chatFields['user_id_receive'] = $userReceive;
        $chatFields['company_id'] = $companySend;
        $chatFields['company_id_receive'] = $companyReceive;

        $chat = Chat::where('chatsable_id', $chatFields['chatsable_id'])
                    ->where('chatsable_type', $chatFields['chatsable_type'])
                    ->where('company_id', $chatFields['company_id'])
                    ->where('company_id_receive', $chatFields['company_id_receive'])
                    ->where([
                                ['user_id_receive', $chatFields['user_id_receive']],
                                ['user_id', $chatFields['user_id']]
                            ]) 
                    ->orWhere([
                                ['user_id_receive', $chatFields['user_id']], 
                                ['user_id', $chatFields['user_id_receive']]
                             ])
                    ->first();
        
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

            DB::commit();
        }
        
        // 
        $chatTransform = new ChatTransformer();
        return $this->showOneData( $chatTransform->transform($chat), 201);
    }
}
