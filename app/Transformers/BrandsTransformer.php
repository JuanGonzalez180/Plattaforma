<?php

namespace App\Transformers;

use App\Models\Brands;
use League\Fractal\TransformerAbstract;

class BrandsTransformer extends TransformerAbstract
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
    public function transform(Brands $brand)
    {
        return [
            'id' => (int)$brand->id,
            'user_id' => (int)$brand->user_id,
            'name' => (string)$brand->name,
            'status' => (string)$brand->status,
            'created_at'=> (string)$brand->created_at,
            'updated_at'=> (string)$brand->updated_at,
            'image'=> $brand->image,
        ];
    }
}
