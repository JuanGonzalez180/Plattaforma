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
    public function transform(Brands $brands)
    {
        return [
            'user_id', => (int)$brands->id,
            'name', => (string)$brands->name,
            'status' => (string)$brands->status,
        ];
    }
}
