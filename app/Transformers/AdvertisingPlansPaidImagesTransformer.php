<?php

namespace App\Transformers;

use App\Models\AdvertisingPlansPaidImages;
use League\Fractal\TransformerAbstract;


class AdvertisingPlansPaidImagesTransformer extends TransformerAbstract
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
    public function transform(AdvertisingPlansPaidImages $advertisingPlansPaidImages)
    {
        return [
            'id' => (int)$advertisingPlansPaidImages->id,
            'advertisings_id' => (int)$advertisingPlansPaidImages->advertisings_id,
            'adver_plans_images_id' => (int)$advertisingPlansPaidImages->adver_plans_images_id,
            'created_at'=> (string)$advertisingPlansPaidImages->created_at,
            'updated_at'=> (string)$advertisingPlansPaidImages->updated_at
        ];
    }
}
