<?php

namespace App\Transformers;

use App\Models\QuotesCompanies;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class QuotesCompaniesTransformer extends TransformerAbstract
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
    public function transform(QuotesCompanies $quotesCompanies)
    {
        $companyTransformer = new CompanyTransformer();

        return [
            'id'            => (int)$quotesCompanies->id,
            'company_id'    => (int)$quotesCompanies->company_id,
            'company'       => $companyTransformer->transform($quotesCompanies->company),
            'quotes_id'     => (int)$quotesCompanies->quotes_id,
            'quote'         => $quotesCompanies->quotes,
            'type'          => (string)$quotesCompanies->type,
            'price'         => $quotesCompanies->priceTransformer(),
            'commission'    => (int)$quotesCompanies->commission,
            'status'        => (string)$quotesCompanies->status,
            'winner'        => (string)$quotesCompanies->winner,
            'created_at'    => (string)$quotesCompanies->created_at,
            'updated_at'    => (string)$quotesCompanies->updated_at,
        ];
    }
}
