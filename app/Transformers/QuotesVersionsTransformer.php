<?php

namespace App\Transformers;

use App\Models\QuotesVersions;
use League\Fractal\TransformerAbstract;

class QuotesVersionsTransformer extends TransformerAbstract
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
    public function transform(QuotesVersions $quotesVersions)
    {
        return [
            'id'        => (int)$quotesVersions->id,
            'quotes_id' => (int)$quotesVersions->quotes_id,
            'adenda'    => (string)$quotesVersions->adenda,
            'price'     => (int)$quotesVersions->price,
            'date'      => (string)$quotesVersions->date,
            'hour'      => (string)$quotesVersions->hour,
            'status'    => (string)$quotesVersions->status
        ];
    }
}
