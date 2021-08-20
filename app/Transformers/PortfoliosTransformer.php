<?php

namespace App\Transformers;


use App\Models\Portfolio;
use App\Transformers\UserTransformer;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class PortfoliosTransformer extends TransformerAbstract
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
    public function transform(Portfolio $portfolio)
    {
        $userTransform = new UserTransformer();
        $companyTransform = new CompanyTransformer();

        return [
            'id' => (int)$portfolio->id,
            'name' => (string)$portfolio->name,
            'description_short' => (string)$portfolio->description_short,
            'description' => (string)$portfolio->description,
            'status' => (string)$portfolio->status,
            'user_id' => (int)$portfolio->user_id,
            'company_id' => (int)$portfolio->company_id,
            'image'=> $portfolio->image,
            'files'=> $portfolio->files,
            'user'=> $userTransform->transform($portfolio->user),
            'created_at'=> (string)$portfolio->created_at,
            'updated_at'=> (string)$portfolio->updated_at
        ];
    }
}
