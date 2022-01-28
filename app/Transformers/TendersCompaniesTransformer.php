<?php

namespace App\Transformers;

use App\Models\TendersCompanies;
use App\Transformers\TendersTransformer;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class TendersCompaniesTransformer extends TransformerAbstract
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
        $tenderTransformer = new TendersTransformer();
        $companyTransformer = new CompanyTransformer();

        return [
            'id'            => (int)$tendersCompanies->id,
            'company_id'    => (int)$tendersCompanies->company_id,
            'company'       => $companyTransformer->transform($tendersCompanies->company),
            'tender_id'     => (int)$tendersCompanies->tender_id,
            'tender'        => $tendersCompanies->tenders,
            'type'          => (string)$tendersCompanies->type,
            'price'         => $tendersCompanies->priceTransformer(),
            'status'        => (string)$tendersCompanies->status,
            'winner'        => (string)$tendersCompanies->winner,
            'created_at'    => (string)$tendersCompanies->created_at,
            'updated_at'    => (string)$tendersCompanies->updated_at,
        ];
    }

}
