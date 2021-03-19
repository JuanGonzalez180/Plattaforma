<?php

namespace App\Http\Controllers\ApiControllers\user;

use App\Models\User;
use App\Models\Company;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UsersController extends ApiController
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            // 7 días
            JWTAuth::factory()->setTTL( 60 * 24 * 7 );
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->errorResponse( [ 'error' => ['invalid_credentials']], 401 );
            }
            $user = User::where('email', $request['email'])->first();
            
            // Si es el administrador de la compañía
            $user['admin'] = false;
            $user['type'] = '';

            // Validar Usuario.
            if( count($user->company) && $user->company[0] ){
                $user['admin'] = true;

                $company = $user->company[0];
                $company->imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
                $company->image;

                if( $company->status !== Company::COMPANY_APPROVED && $company->type_entity->type->slug == 'demanda' ){
                    $user['type'] = 'demanda';
                    return $this->errorResponse( [ 'not_approved_a' => ['not_approved']], 500 );
                }elseif( $company->status !== Company::COMPANY_APPROVED){
                    $user['type'] = 'oferta';
                    return $this->errorResponse( [ 'not_approved_b' => ['not_approved']], 500 );
                }
            }

            $user->image;
        } catch (JWTException $e) {
            return $this->errorResponse( [ 'error' => ['could_not_create_token']], 500 );
        }
        return response()->json(compact('token','user'));
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }
}
