<?php

namespace App\Transformers;

use App\Models\QueryWall;
use App\Transformers\UserTransformer;
use League\Fractal\TransformerAbstract;

class QueryWallTransformer extends TransformerAbstract
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
    public function transform(QueryWall $querywall)
    {
        $userTransform = new UserTransformer();

        return [
            'id' => (int)$querywall->id,
            'querysable_id'=> (int)$querywall->querysable_id,
            // 'querysable_type'=> (string)$querywall->querysable_type,
            'company_id'=> (int)$querywall->company_id,
            'company'=> $querywall->company,
            'question'=> (string)$querywall->question,
            'answer'=> (string)$querywall->answer,
            'user_id'=> (int)$querywall->user_id,
            'user'=> $userTransform->transform($querywall->user),
            'status'=> (string)$querywall->status,
            'visible'=> (string)$querywall->visible,
            'created_at'=> (string)$querywall->created_at
        ];
    }
}
