<?php

namespace App\Transformers;

use App\Models\Tenders;
use App\Transformers\UserTransformer;
use App\Transformers\CompanyTransformer;
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
        $userTransform = new UserTransformer();
        return [
            //
            'id' => (int)$tender->id,
            'user_id'=> (int)$tender->user_id,
            'company_id'=> (int)$tender->company_id,
            'project'=> $tender->project,
            'categories'=> $tender->categories,
            'tags'=> $tender->tendersVersionLastPublishTags(),
            'project_id'=> (int)$tender->project_id,
            'name'=> (string)$tender->name,
            'status'=> (string)$tender->status,
            'test'=> (string)$tender->test,
            'company_status'=> (string)$tender->company_status,
            'price'=> (string)$tender->price,
            'description'=> (string)$tender->description,
            'adenda'=> (string)$tender->adenda,
            'created_at'=> (string)$tender->created_at,
            'updated_at'=> (string)$tender->updated_at,
            'date'=> (string)$tender->date,
            'hour'=> (string)$tender->hour,
            'user'=> $userTransform->transform($tender->user),
            'tendersVersionLast'=> $tender->tendersVersionLast(),
            'tendersList'=> $tender->tendersVersion,
            'tendersVersionLastPublish'=> $tender->tendersVersionLastPublish(),
            'tenderStatusUser'=> $tender->tenderStatusUser(),
            'slugTender'     => (string)$tender->company->slug,
            'tendersVersionCount'=> count($tender->tendersVersion),
            'tenderType'=> $tender->type,
            // 'commissionedUsers'=> $tender->commissionedUsers()
            'participatingUsers'=> $tender->participatingUsers(),
            'company_image'=> $tender->company->image
        ];
    }

    public function transformDetail(Tenders $tender)
    {
        $userTransform = new UserTransformer();
        $companyTransformer = new CompanyTransformer();
        $projectsTransformer = new ProjectsTransformer();

        return [
            //
            'id' => (int)$tender->id,
            'user_id'=> (int)$tender->user_id,
            'company_id'=> (int)$tender->company_id,
            'company' => $companyTransformer->transform($tender->company),
            'project_id'=> (int)$tender->project_id,
            'project'=> $projectsTransformer->transform($tender->project),
            'categories'=> $tender->categories,
            'name'=> (string)$tender->name,
            'status'=> (string)$tender->status,
            'test'=> (string)$tender->test,
            'company_status'=> (string)$tender->company_status,
            'price'=> (string)$tender->price,
            'description'=> (string)$tender->description,
            'adenda'=> (string)$tender->adenda,
            'created_at'=> (string)$tender->created_at,
            'updated_at'=> (string)$tender->updated_at,
            'date'=> (string)$tender->date,
            'hour'=> (string)$tender->hour,
            'user'=> $userTransform->transform($tender->user),
            'tendersVersionLast'=> $tender->tendersVersionLast(),
            'tendersVersionLastPublish'=> $tender->tendersVersionLastPublish(),
            'tendersVersionCount'=> count($tender->tendersVersion),
            'tendersVersionList'=> $tender->tendersVersion->sortBy([ ['created_at', 'desc'] ]),
            'tenderStatusUser'=> $tender->tenderStatusUser(),
            'slugTender'     => (string)$tender->company->slug,
            'tenderType'=> (string)$tender->type,
            // 'commissionedUsers'=> $tender->commissionedUsers()
            'participatingUsers'=> $tender->participatingUsers()
        ];
    }
}
