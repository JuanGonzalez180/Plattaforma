<?php

namespace App\Transformers;

use App\Models\Products;
use League\Fractal\TransformerAbstract;

class ProductsTransformer extends TransformerAbstract
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
    public function transform(Products $product)
    {
        return [
            //
            'id' => (int)$product->id,
            'user_id'=> (int)$product->user_id,
            'company_id'=> (int)$product->company_id,
            'name'=> (string)$product->name,
            'type'=> (string)$product->type,
            'status'=> ((string)$product->status == Products::PRODUCT_ERASER) ? 'inactivo' : 'activo',
            'description'=> (string)$product->description,
            'created_at'=> (string)$product->created_at,
            'updated_at'=> (string)$product->updated_at,
            'user'=> $product->user,
        ];
    }
}
