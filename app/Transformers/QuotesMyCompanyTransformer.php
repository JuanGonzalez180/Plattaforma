<?php

namespace App\Transformers;

use App\Models\QuotesCompanies;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class QuotesMyCompanyTransformer extends TransformerAbstract
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
        return [
            'id'                    => (int)$quotesCompanies->id,
            'quote_id'             => (int)$quotesCompanies->quote->id,
            'quote_type'           => (string)$quotesCompanies->quote->type,
            'company_id'            => (int)$quotesCompanies->company_id,
            'slug_company'          => (string)$quotesCompanies->company->slug,
            'slug_quote'           => (string)$quotesCompanies->quote->company->slug,
            'quote_name'           => (string)$quotesCompanies->quote->name,
            'quote_status'         => (string)$quotesCompanies->quote->quotesVersionLastPublish()->status,
            'quote_company_status' => (string)$quotesCompanies->status,
            'winner'                => $quotesCompanies->winner,
            'project_name'          => (string)$quotesCompanies->quote->project->name,
            'closing_date'          => (string)$quotesCompanies->quote->quotesVersionLastPublish()->date,
            'closing_hour'          => (string)$quotesCompanies->quote->quotesVersionLastPublish()->hour
        ];
    }
}
