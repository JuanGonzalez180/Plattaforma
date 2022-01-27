<?php

namespace App\Transformers;

use App\Models\TendersCompanies;
use App\Transformers\TendersTransformer;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class TendersCompanySelectedTransformer extends TransformerAbstract
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
    public function transform(TendersCompanies $tendersCompanies)
    {
        return [
            'id'                    => (int)$tendersCompanies->id,
            'user_id'               => (int)$tendersCompanies->user_id,
            'tender_id'             => (int)$tendersCompanies->tender->id,
            'company_id'            => (int)$tendersCompanies->company_id,
            'user_fullname'         => (string)$tendersCompanies->user->fullName(),
            'tender_name'           => (string)$tendersCompanies->tender->name,
            'company_name'          => (string)$tendersCompanies->company->name,
            'tender_company_price'  => (int)$tendersCompanies->price,
            'files'                 => $tendersCompanies->files
        ];
    }
}
