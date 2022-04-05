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
            'tender_type'           => (int)$tendersCompanies->tender->type,
            'company_id'            => (int)$tendersCompanies->company_id,
            'slug_company'          => (string)$tendersCompanies->company->slug,
            'slug_tender'           => (string)$tendersCompanies->tender->company->slug,
            'tender_name'           => (string)$tendersCompanies->tender->name,
            'tender_status'         => (string)$tendersCompanies->tender->tendersVersionLastPublish()->status,
            'tender_company_status' => (string)$tendersCompanies->status,
            'winner'                => $tendersCompanies->winner,
            'project_name'          => (string)$tendersCompanies->tender->project->name,
            'closing_date'          => (string)$tendersCompanies->tender->tendersVersionLastPublish()->date,
            'closing_hour'          => (string)$tendersCompanies->tender->tendersVersionLastPublish()->hour
        ];
    }
}
