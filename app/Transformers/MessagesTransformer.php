<?php

namespace App\Transformers;

use App\Models\Messages;
use League\Fractal\TransformerAbstract;

class MessagesTransformer extends TransformerAbstract
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
    public function transform(Messages $message)
    {
        return [
            //
            'id' => (int)$message->id,
            'chat_id'=> (int)$message->chat_id,
            'user_id'=> (int)$message->user_id,
            'message'=> (string)$message->message,
            'status'=> (string)$message->status,
            'viewed'=> (string)$message->viewed,
            'type'=> (string)$message->type,
            'created_at'=> (string)$message->created_at
        ];
    }
}