<?php

namespace App\Transformers;

use App\Models\Projects;
use App\Transformers\UserTransformer;
use League\Fractal\TransformerAbstract;

class ProjectsTransformer extends TransformerAbstract
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
    public function transform(Projects $project)
    {   
        $userTransform = new UserTransformer();
        return [
            //
            'id' => (int)$project->id,
            'user_id'=> (int)$project->user_id,
            'company_id'=> (int)$project->company_id,
            'name'=> (string)$project->name,
            'status'=> (string)$project->status,
            'meters'=> (string)$project->meters,
            'description'=> (string)$project->description,
            'created_at'=> (string)$project->created_at,
            'updated_at'=> (string)$project->updated_at,
            'date_start'=> (string)$project->date_start,
            'date_end'=> (string)$project->date_end,
            'user'=> $userTransform->transform($project->user),
        ];
    }
}
