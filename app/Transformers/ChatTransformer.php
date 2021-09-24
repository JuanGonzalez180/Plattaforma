<?php

namespace App\Transformers;

use App\Models\Chat;
use App\Transformers\UserTransformer;
use League\Fractal\TransformerAbstract;

class ChatTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Chat $chat)
    {
        $userTransform  = new UserTransformer();

        return [
            //
            'id' => (int)$chat->id,
            'name'=> (string)$chat->name,
            'company_send'=> (string)$chat->company_id,
            'company_receive'=> (string)$chat->company_id_receive,
            'user_send'=> (string)$chat->user_id,
            'user_receive'=> (string)$chat->user_id_receive,
            'notviewed'=> (int)$chat->notviewed,
            'data'=> $chat->data,
            'message'=> $chat->message,
            'user'=> $userTransform->transform($chat->user),
            'userReceive'=> $userTransform->transform($chat->userReceive),
            'updated_at'=> $chat->updated_at,
            'updated_at_new'=> $chat->updated_at_new
        ];
    }
}