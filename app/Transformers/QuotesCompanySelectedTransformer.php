<?php

namespace App\Transformers;

use App\Models\QuotesCompanies;
use App\Transformers\QuotesTransformer;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class QuotesCompanySelectedTransformer extends TransformerAbstract
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
            'id'                         => (int)$quotesCompanies->id,
            'user_id'                    => (int)$quotesCompanies->user_id,
            'quote_id'                  => (int)$quotesCompanies->quote->id,
            'company_id'                 => (int)$quotesCompanies->company_id,
            'user_fullname'              => (string)$quotesCompanies->user->fullName(),
            'image'                      => $quotesCompanies->company->image,
            'quote_name'                => (string)$quotesCompanies->quote->name,
            'company_name'               => (string)$quotesCompanies->company->name,
            'quote_company_price'       => (int)$quotesCompanies->price,
            'quote_company_commission'  => (int)$quotesCompanies->commission,
            'files'                      => $quotesCompanies->files
        ];
    }
}
