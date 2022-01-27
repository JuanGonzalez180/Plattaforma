<?php

namespace App\Http\Controllers\ApiControllers\notifications;

use JWTAuth;
use App\Models\User;
use App\Models\UsersToken;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class UsersTokensController extends ApiController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function store(Request $request){
        $user = $this->validateUser();

        $rules = [
            'token' => 'required'
        ];

        if( !$request->token ){
            return [];
        }
        $tokenFields = $request->all();
        $tokenFields['token'] = $request->token;
        $tokenFields['type'] = $request->type ? $request->type : UsersToken::TYPE_FIREBASE;
        $tokenFields['user_id'] = $user->id;
        $tokenFields['device'] = $request->device;
        $tokenFields['platform'] = $request->platform;
        $tokenFields['version'] = $request->version;

        $existToken = UsersToken::where( 'token', $request->token )
                    ->first();

                    
        try{
            if( $existToken && $existToken->id ){
                $token = $existToken;
                $token->update( $tokenFields );
            }else{
                $token = UsersToken::create( $tokenFields );
            }
        }catch(\Throwable $th){
            DB::rollBack();
            $tokenError = [ 'token' => 'Error, no se ha podido crear el token' ];
            return $this->errorResponse( $tokenError, 500 );
        }

        DB::commit();
        return $this->showOne($token,201);
    }

    public function sendWebNotification( Request $request  ){
        $url = 'https://fcm.googleapis.com/fcm/send';
        // $FcmToken = User::whereNotNull('device_key')->pluck('device_key')->all();
        $FcmToken = ['eENlPVbmt4-Xwu4nX-igVa:APA91bGXbJ7TZUFO0Ikcfk_nDNqJyAu2NkkwJcr3RGtzJzOBSeqincQ6TL8KPDcMwWLTQEHf0OamcMLYv8durgMfC5QbeRSIAhgJCD2bI28gCehrlyYNECXNT_crnl9fIRdM7m_zP72z'];
          
        $serverKey = env('FIREBASE_SECRET');
        
        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,  
            ]
        ];
        $encodedData = json_encode($data);
    
        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
      
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        // Execute post
        $result = curl_exec($ch);

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }        

        // Close connection
        curl_close($ch);

        // FCM response
        dd($result);
    }
}
