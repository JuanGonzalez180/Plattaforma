<?php

namespace App\Http\Controllers\ApiControllers\script;

use App\Transformers\UserTransformer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class scriptController extends Controller
{
    public function __invoke(Request $request)
    {
        // Solo para listar todos los usuarios para el comet chat.
        return $this->getAllUsersCometChat();
    }

    public function getAllUsersCometChat()
    {
        $users              = User::whereNotIn('id',[1])->get();
        $userTransform      = new UserTransformer();
        $userCometChat      = [];

        foreach($users as $user)
        {
            $userCometChat[] = $userTransform->transformCometChat($user);
        }
        return $userCometChat;
    }
}
