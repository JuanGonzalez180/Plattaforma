<?php

namespace App\Transformers;

use App\Models\Advertisings;
use League\Fractal\TransformerAbstract;

class AdvertisingsTransformer extends TransformerAbstract
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
    public function transform(Advertisings $advertising)
    {
        return [
            'id' => (int)$advertising->id,
            'name' => (string)$advertising->name,
            'plan' => (int)$advertising->plan_id,
            'action_id' => (int)$advertising->advertisingable_id,
            'action_type' => (string)$advertising->advertisingable_type,
            'start_date' => (string)$advertising->start_date,
            'start_time' => (string)$advertising->start_time,
            'created_at'=> (string)$advertising->created_at,
            'updated_at'=> (string)$advertising->updated_at
        ];
    }
}
