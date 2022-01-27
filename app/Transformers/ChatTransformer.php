<?php

namespace App\Transformers;

use App\Models\Chat;
use App\Models\User;
use App\Models\Company;
use App\Transformers\UserTransformer;
use App\Transformers\CompanyTransformer;
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
        $companyTransform  = new CompanyTransformer();

        return [
            //
            'id' => (int)$chat->id,
            'name'=> (string)$chat->name,
            'company_send'=> (string)$chat->company_id,
            'company_receive'=> (string)$chat->company_id_receive,
            'companyAll'=> $companyTransform->transform( Company::find($chat->company_id) ),
            'companyAllReceive'=> $companyTransform->transform( Company::find($chat->company_id_receive)),
            'user_send'=> (string)$chat->user_id,
            'user_receive'=> (string)$chat->user_id_receive,
            'notviewed'=> (int)$chat->notviewed,
            'data'=> $chat->chatData(),
            'user'=> $userTransform->transform( User::find($chat->user_id) ),
            'userReceive'=> $userTransform->transform( User::find($chat->user_id_receive) ),
            'updated_at'=> $chat->updated_at,
            'updated_at_new'=> $chat->updated_at_new
        ];
    }
}