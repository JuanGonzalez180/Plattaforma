<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class AccountEditController extends ApiController
{
    //
    public $routeFile = 'public/';

    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $rules = [
                'name' => ['max:255'],
                'lastname' => ['max:255'],
                'username' => ['alpha_dash','max:255', Rule::unique('users')->ignore($user->id)],
                'email' => ['email', Rule::unique('users')->ignore($user->id) ],
            ];

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

        }
        $this->validate( $request, $rules );

        if( $request->username )
            $user->username = $request->username;

        if( $request->email )
            $user->email = $request->email;

        if( $request->name )
            $user->name = $request->name;

        if( $request->lastname )
            $user->lastname = $request->lastname;
        
        if( $request->image ){
            $png_url = "perfil-".time().".jpg";
            $img = $request->image;
            $img = substr($img, strpos($img, ",")+1);
            $data = base64_decode($img);
            
            $routeFile = 'images/users/'.$user->id.'/'.$png_url;
            Storage::disk('local')->put( $this->routeFile . $routeFile, $data);

            if( !$user->image ){
                $user->image()->create(['url' => $routeFile]);
            }else{
                Storage::disk('local')->delete( $this->routeFile . $user->image->url );
                $user->image()->update(['url' => $routeFile]);
            }
        }

        $user->save();
        // ReSearch User
        $userNew = User::findOrFail($user->id);
        $userNew->image;

        return $this->showOne($userNew,200);
    }
}
