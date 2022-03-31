<?php

namespace App\Transformers;

use App\Models\Notifications;
use League\Fractal\TransformerAbstract;

class NotificationsTransformer extends TransformerAbstract
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
    public function transform(Notifications $notification)
    {
        return [
            //
            'id' => (int)$notification->id,
            'user_id' => (int)$notification->user_id,
            'title'=> (string)$notification->title,
            'query_id'=> $notification->queryId(),
            'type'=> (string)$notification->type,
            'subtitle'=> (string)$notification->subtitle,
            'message'=> (string)$notification->message,
            'viewed'=> (boolean)$notification->viewed,
            'created_at'=> (string)$notification->created_at,
            'updated_at'=> (string)$notification->updated_at,
        ];
    }
}
