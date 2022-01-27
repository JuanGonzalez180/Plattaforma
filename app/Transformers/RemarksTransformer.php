<?php

namespace App\Transformers;


use App\Models\Remarks;
use App\Transformers\UserTransformer;
use League\Fractal\TransformerAbstract;

class RemarksTransformer extends TransformerAbstract
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
    public function transform(Remarks $remarks)
    {
        $userTransform = new UserTransformer();

        return [
            'id' => (int)$remarks->id,
            'calification' => (string)$remarks->calification,
            'message' => (string)$remarks->message,
            'status' => (string)$remarks->status,
            'user_id' => (int)$remarks->user_id,
            'company_id' => (int)$remarks->company_id,
            'user'=> $userTransform->transform($remarks->user),
            'created_at'=> (string)$remarks->created_at,
            'updated_at'=> (string)$remarks->updated_at
        ];
    }
}
