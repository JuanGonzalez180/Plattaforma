<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
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
    public function transform(User $user)
    {
        return [
            //
            'id' => (int)$user->id,
            'name' => (string)$user->name,
            'lastname'=> (string)$user->lastname,
            'created_at'=> (string)$user->created_at,
            'updated_at'=> (string)$user->updated_at,
            'url'=> (string)$user->image ? url( 'storage/' . $user->image->url ) : null
        ];
    }
}
