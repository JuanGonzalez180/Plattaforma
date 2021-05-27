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
            'slug'=> (string)$company->slug,
            'image'=> $company->image,
            'coverpage'=> Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first()
        ];
    }
}