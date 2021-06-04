<?php

namespace App\Transformers;

use App\Models\Company;
use League\Fractal\TransformerAbstract;
use App\Transformers\TeamTransformer;

class CompanyDetailTransformer extends TransformerAbstract
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
    public function transform(Company $company)
    {
        $teamTransform = new TeamTransformer();
        return [
            //
            'id' => (int)$company->id,
            'name'=> (string)$company->name,
            'description'=> (string)$company->description,
            'slug'=> (string)$company->slug,
            'image'=> $company->image,
            'coverpage'=> $company->coverpage,
            'address'=> $company->address,
            'portfolio'=> $company->files,
            'services'=> $company->companyCategoryServices,
            'team'=> $teamTransform->transformNoDetail($company->team),
            'projects'=> $company->projects,
            'tenders'=> $company->tenders,
            'products'=> $company->products,
            'blogs'=> $company->blogs,
            'total'=> $company->total(),
        ];
    }
}
