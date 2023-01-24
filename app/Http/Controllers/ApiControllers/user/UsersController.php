<?php

namespace App\Http\Controllers\ApiControllers\user;

use JWTAuth;
use App\Models\User;
use App\Models\Team;
use App\Models\Image;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\ApiControllers\ApiController;

class UsersController extends ApiController
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            // var_dump(User::USER_ADMIN);
            // 7 días
            JWTAuth::factory()->setTTL( 60 * 24 * 7 );
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->errorResponse( [ 'error' => ['invalid_credentials']], 401 );
            }
            $user = User::where('email', $request['email'])->first();
            
            if( $user->admin == User::USER_ADMIN ) {
                return $this->errorResponse( [ 'error' => ['invalid_credentials']], 500 );
            }
            
            // Si es el administrador de la compañía
            $user['admin']  = false;
            $user['type']   = '';

            // Validar Usuario.
            if( $user->isAdminFrontEnd() )
            {
                $user['admin'] = true;
                
                $company = $user->company[0];
                $company->imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
                $company->image;
                $user['type'] = $company->type_entity->type->slug;

                if( $company->status !== Company::COMPANY_APPROVED && $company->type_entity->type->slug == 'demanda' ){
                    return $this->errorResponse( [ 'not_approved' => [$company->status]], 500 );
                }elseif( $company->status !== Company::COMPANY_APPROVED){
                    $user['type'] = 'oferta';
                    return $this->errorResponse( [ 'not_approved' => [$company->status]], 500 );
                }
                $user->slug = $company->slug;
            }
            elseif( $user->team )
            {
                if( $user->team->status == Team::TEAM_PENDING ) {
                    return $this->errorResponse( [ 'team_pending' => ['not_approved']], 500 );
                }

                $company = $user->team->company;
                $user['type'] = $company->type_entity->type->slug;
                $user->slug = $company->slug;
                $user->image;
                $user['charge'] = isset($user->team)? $user->team->position : '';
            }
            
            $user->adminUser = $company->user;
            if( $user->adminUser )
            {
                $user->adminUser->url = (string)$user->adminUser->image ? url( 'storage/' . $user->adminUser->image->url ) : '';
                $user->adminUser->charge = isset($user->adminUser->team)? $user->adminUser->team->position : '';
            }
            
            
            
        } catch (JWTException $e) {
            return $this->errorResponse( [ 'error' => ['could_not_create_token']], 500 );
        }

        $status = $this->statusCompany($user);



        return response()->json(compact('token','user','status'));
    }

    public function statusCompany($user)
    {
        if( $user->isAdminFrontEnd() ){
            $company = $user->company[0];
        }elseif( $user->team ){
            $company = $user->team->company;
        }

        return $company->companyStatusPayment();
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
