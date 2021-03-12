<?php

namespace App\Http\Controllers\ApiControllers\myaccount;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class SendInvitationController extends ApiController
{
    public function store(Request $request)
    {
        // Mail::to($user->email)->send(new SendCode( $code, $minutes, $user ));
        return '';
    }
}
