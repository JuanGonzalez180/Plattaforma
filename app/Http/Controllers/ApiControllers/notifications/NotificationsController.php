<?php

namespace App\Http\Controllers\ApiControllers\notifications;

use JWTAuth;
use App\Models\User;
use App\Models\Tenders;
use App\Models\TendersCompanies;
use App\Models\Notifications;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class NotificationsController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index(){
        $user = $this->validateUser();
        
        $notifications = $user->notifications
                              ->sortBy([ ['created_at', 'desc'] ]);

        return $this->showAllPaginate($notifications);
    }
    
    public function destroy($id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $notification = Notifications::findOrFail($id);
        $notification->delete();

        return $this->showOneData( ['success' => 'Se ha eliminado correctamente la notificación', 'code' => 200 ], 200);
    }
}
