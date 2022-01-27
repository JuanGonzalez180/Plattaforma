<?php

namespace App\Transformers;

use App\Models\AdvertisingPlans;
use League\Fractal\TransformerAbstract;

class AdvertisingPlansTransformer extends TransformerAbstract
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
    public function transform(AdvertisingPlans $advertisingPlan)
    {
        return [
            'id' => (int)$advertisingPlan->id,
            'name' => (string)$advertisingPlan->name,
            'description' => (string)$advertisingPlan->description,
            'days' => (int)$advertisingPlan->days,
            'price' => (double)$advertisingPlan->price,
            'type_ubication' => (string)$advertisingPlan->type_ubication,
            'image' => $advertisingPlan->image,
            'created_at'=> (string)$advertisingPlan->created_at,
            'updated_at'=> (string)$advertisingPlan->updated_at
        ];
    }
}
