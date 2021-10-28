<?php

namespace App\Transformers;

use App\Models\Advertisings;
use League\Fractal\TransformerAbstract;
use App\Transformers\AdvertisingPlansPaidImagesTransformer;

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
        $images = [];
        if( $advertising->advertisingPlansPaidImages->first() ){
            $transformer = $advertising->advertisingPlansPaidImages->first()->transformer;
            $transformation = fractal(  $advertising->advertisingPlansPaidImages, new $transformer );
            $images = $transformation->toArray();
        }

        return [
            'id' => (int)$advertising->id,
            'name' => (string)$advertising->name,
            'plan' => $advertising->plan,
            'plan_paid_images' => $images,
            // 'plan_paid_images' => $advertising->advertisingPlansPaidImages,
            'payments' => $advertising->payments,
            'plan_id' => (int)$advertising->plan_id,
            'action_id' => (int)$advertising->advertisingable_id,
            'action_type' => (string)$advertising->advertisingable_type,
            'status' => $advertising->status(),
            'start_date' => (string)$advertising->start_date,
            'start_time' => (string)$advertising->start_time,
            'created_at'=> (string)$advertising->created_at,
            'updated_at'=> (string)$advertising->updated_at
        ];
    }
}
