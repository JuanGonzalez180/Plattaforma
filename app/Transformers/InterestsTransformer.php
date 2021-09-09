<?php

namespace App\Transformers;


use App\Models\Interests;
use App\Transformers\UserTransformer;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class InterestsTransformer extends TransformerAbstract
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
    public function transform(Interests $interests)
    {
        $userTransform = new UserTransformer();
        $companyTransform = new CompanyTransformer();

        return [
            'id' => (int)$interests->id,
            'user_id' => (int)$interests->user_id,
            'user'=> $userTransform->transform($interests->user),
            'query_id'=> (string)$interests->query_id,
            'type'=> (string)$interests->type,
            'name'=> (string)$interests->name,
            'image'=> $interests->image,
            'company'=> ($interests->company) ? $companyTransform->transform($interests->company) : [],
            'created_at'=> (string)$interests->created_at,
            'updated_at'=> (string)$interests->updated_at
        ];
    }
}
