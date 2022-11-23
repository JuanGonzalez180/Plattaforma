<?php

namespace App\Transformers;

use App\Models\Company;
use League\Fractal\TransformerAbstract;
use App\Transformers\TeamTransformer;
use App\Transformers\UserTransformer;

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
        $teamTransform      = new TeamTransformer();
        $userTransform      = new UserTransformer();

        $users['admin'] = $userTransform->transform($company->user);
        $users['teams'] = $teamTransform->transformNoDetail($company->team);

        return [
            //
            'id' => (int)$company->id,
            'name'=> (string)$company->name,
            'description'=> (string)$company->description,
            'slug'=> (string)$company->slug,
            'image'=> $company->image,
            'coverpage'=> $company->coverpage,
            'address'=> $company->address,
            'portfolio'=> $company->portfolios,
            'catalogs'=> $company->catalogs,
            'services'=> $company->companyCategoryServices,
            'tags'=> $company->tags,
            'team'=> $teamTransform->transformNoDetail($company->team),
            'teams'=> $users,
            'projects'=> $company->projects,
            'tenders'=> $company->tenders,
            'quotes'=> $company->quotes,
            'products'=> $company->products,
            'blogs'=> $company->blogs,
            'remarks'=> $company->remarks,
            'calification'=> $company->calification(),
            'total'=> $company->total(),
        ];
    }
}
