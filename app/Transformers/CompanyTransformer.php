<?php

namespace App\Transformers;

use App\Models\Image;
use App\Models\Company;
use League\Fractal\TransformerAbstract;

class CompanyTransformer extends TransformerAbstract
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
        return [
            //
            'id' => (int)$company->id,
            'name'=> (string)$company->name,
            'description'=> (string)$company->description,
            'slug'=> (string)$company->slug,
            'image'=> $company->image,
            'created_at'=> (string)$company->created_at,
            'updated_at'=> (string)$company->updated_at,
            'calification'=> $company->calification(),
            'user_admin' => $company->user->id ,
            'coverpage'=> Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first()
        ];
    }
}
