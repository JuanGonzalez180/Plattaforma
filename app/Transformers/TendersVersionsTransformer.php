<?php

namespace App\Transformers;

use App\Models\TendersVersions;
use League\Fractal\TransformerAbstract;

class TendersVersionsTransformer extends TransformerAbstract
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
    public function transform(TendersVersions $tendersVersions)
    {
        return [
            'id'        => (int)$tendersVersions->id,
            'tenders_id'=> (int)$tendersVersions->tenders_id,
            'adenda'    => (string)$tendersVersions->adenda,
            'price'     => (int)$tendersVersions->price,
            'date'      => (string)$tendersVersions->date,
            'hour'      => (string)$tendersVersions->hour,
            'status'    => (string)$tendersVersions->status
        ];
    }
}
