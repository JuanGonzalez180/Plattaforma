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

        //no vistas
        $notificationsNotViewed = $this->getNotification($user->id, 0);
        //vistas
        $notificationsViewed    = $this->getNotification($user->id, 1);

        $notifications = $notificationsNotViewed->merge($notificationsViewed);

        return $this->showAllPaginateSetTotal($notifications, 200, $notificationsNotViewed->count());
    }

    public function getNotification($user_id,$status)
    {
        return Notifications::where('user_id', $user_id)
            ->where('viewed',$status)
            ->orderBy('updated_at','desc')
            ->get();
    }
    
    public function destroy($id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $notification = Notifications::findOrFail($id);
        $notification->delete();

        return $this->showOneData( ['success' => 'Se ha eliminado correctamente la notificación', 'code' => 200 ], 200);
    }

    public function update($id)
    {
        $notification = Notifications::find($id);
        $notification->viewed = 1;
        $notification->save();

        return $this->showOne($notification,200);
    }
}
