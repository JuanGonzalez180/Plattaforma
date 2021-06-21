<?php

namespace App\Transformers;

use App\Models\TendersCompanies;
use App\Transformers\TendersTransformer;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class TendersMyCompanyTransformer extends TransformerAbstract
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
            'tender_id'             => (int)$tendersCompanies->tender->id,
            'company_id'            => (int)$tendersCompanies->company_id,
            'slug_company'          => (string)$tendersCompanies->company->slug,
            'tender_name'           => (string)$tendersCompanies->tender->name,
            'tender_status'         => (string)$tendersCompanies->tender->tendersVersionLast()->status,
            'tender_company_status' => (string)$tendersCompanies->status,
            'project_name'          => (string)$tendersCompanies->tender->project->name,
            'closing_date'          => (string)$tendersCompanies->tender->tendersVersionLast()->date,
            'closing_hour'          => (string)$tendersCompanies->tender->tendersVersionLast()->hour
        ];
    }
}
