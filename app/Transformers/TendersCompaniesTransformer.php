<?php

namespace App\Transformers;

use App\Models\TendersCompanies;
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
        return [
            'id'            => (int)$tendersCompanies->id,
            'tender_id'     => (int)$tendersCompanies->tender_id,
            'company_id'    => (int)$tendersCompanies->company_id,
            'type'          => (int)$tendersCompanies->type,
            'price'         => (string)$tendersCompanies->price,
            'status'        => (string)$tendersCompanies->status,
            'winner'        => (string)$tendersCompanies->winner,
            'company'       => $tendersCompanies->company
        ];
    }
}
