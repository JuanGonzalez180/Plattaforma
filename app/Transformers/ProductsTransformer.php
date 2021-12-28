<?php

namespace App\Transformers;

use App\Models\User;
use App\Models\Company;
use App\Models\Brands;
use App\Models\Products;
use App\Transformers\UserTransformer;
use App\Transformers\CompanyTransformer;
use App\Transformers\BrandsTransformer;
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
        $userTransform = new UserTransformer();
        $companyTransform = new CompanyTransformer();
        return [
            //
            'id' => (int)$product->id,
            'user_id'=> (int)$product->user_id,
            'company_id'=> (int)$product->company_id,
            'name'=> (string)$product->name,
            'code'=> (string)$product->code,
            'type'=> (string)$product->type,
            'status'=> ((string)$product->status == Products::PRODUCT_ERASER) ? 'inactivo' : 'activo',
            'description'=> (string)$product->description,
            'created_at'=> (string)$product->created_at,
            'updated_at'=> (string)$product->updated_at,
            'tags'=> $product->tags,
            'brand'=> $product->brand,
            'user'=> $userTransform->transform($product->user),
            'company'=> $companyTransform->transform($product->company),
            'image'=> $product->image,
        ];
    }

    public function transformDetail(Products $product)
    {
        $userTransform  = new UserTransformer();
        $companyTransform = new CompanyTransformer();
        $brandsTransform = new BrandsTransformer();

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
            'product_categories'=> $product->productCategories,
            'tags'=> $product->tags,
            'user'=> $userTransform->transform($product->user),
            'company'=> $companyTransform->transform($product->company),
            'image'=> $product->image,
            'brand'=> $brandsTransform->transform($product->brand),
            'files'=> $product->files,
        ];
    }
}
