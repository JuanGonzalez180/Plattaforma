<?php

namespace App\Transformers;

use App\Models\Team;
use League\Fractal\TransformerAbstract;

class TeamDetailTransformer extends TransformerAbstract
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
            'status'=> (string)$team->status,
            'url'=> $team->user && $team->user->image ? url( 'storage/' . $team->user->image->url ) : '',
            'name'=> $team->user && $team->user->name ? $team->user->name : '',
            'lastname'=> $team->user && $team->user->lastname ? $team->user->lastname : '',
        ];
    }
}
