<?php

namespace App\Transformers;

use App\Models\Catalogs;
use App\Transformers\UserTransformer;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class CatalogTransformer extends TransformerAbstract
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
    public function transform(Catalogs $catalog)
    {
        $userTransform = new UserTransformer();
        $companyTransform = new CompanyTransformer();

        return [
            'id' => (int)$catalog->id,
            'name' => (string)$catalog->name,
            'description_short' => (string)$catalog->description_short,
            'description' => (string)$catalog->description,
            'status' => (string)$catalog->status,
            'user_id' => (int)$catalog->user_id,
            'company_id' => (int)$catalog->company_id,
            'image'=> $catalog->image,
            'files'=> $catalog->files,
            'user'=> $userTransform->transform($catalog->user),
            'created_at'=> (string)$catalog->created_at,
            'updated_at'=> (string)$catalog->updated_at
        ];
    }
}
