<?php

namespace App\Transformers;

use App\Models\Tenders;
use League\Fractal\TransformerAbstract;

class TendersTransformer extends TransformerAbstract
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
    public function transform(Tenders $tender)
    {
        return [
            //
            'id' => (int)$tender->id,
            'user_id'=> (int)$tender->user_id,
            'company_id'=> (int)$tender->company_id,
            'project'=> $tender->project,
            'categories'=> $tender->categories,
            'project_id'=> (int)$tender->project_id,
            'name'=> (string)$tender->name,
            'status'=> (string)$tender->status,
            'price'=> (string)$tender->price,
            'description'=> (string)$tender->description,
            'adenda'=> (string)$tender->adenda,
            'created_at'=> (string)$tender->created_at,
            'updated_at'=> (string)$tender->updated_at,
            'date'=> (string)$tender->date,
            'hour'=> (string)$tender->hour,
            'user'=> $tender->user,
            'tendersVersionLast'=> $tender->tendersVersionLast(),
            'tendersVersionCount'=> count($tender->tendersVersion),
        ];
    }
}
