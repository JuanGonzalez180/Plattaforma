<?php

namespace App\Transformers;

use App\Models\Team;
use League\Fractal\TransformerAbstract;

class TeamTransformer extends TransformerAbstract
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
    public function transform(Team $team)
    {
        return [
            //
            'id' => (int)$team->id,
            'user_id'=> (int)$team->user_id,
            'company_id'=> (int)$team->company_id,
            'position'=> (string)$team->position,
            'phone'=> (string)$team->phone,
            'status'=> (string)$team->status,
            'created_at'=> (string)$team->created_at,
            'updated_at'=> (string)$team->updated_at,
            'url'=> $team->url,
            'user'=> $team->user,
        ];
    }
}
