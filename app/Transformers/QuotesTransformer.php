<?php

namespace App\Transformers;

use App\Models\Quotes;
use App\Transformers\UserTransformer;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class QuotesTransformer extends TransformerAbstract
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
    public function transform(Quotes $quotes)
    {
        $userTransform = new UserTransformer();
        return [
            //
            'id' => (int)$quotes->id,
            'user_id'=> (int)$quotes->user_id,
            'company_id'=> (int)$quotes->company_id,
            'project'=> $quotes->project,
            'categories'=> $quotes->categories,
            'tags'=> $quotes->quotesVersionLastPublishTags(),
            'project_id'=> (int)$quotes->project_id,
            'name'=> (string)$quotes->name,
            'status'=> (string)$quotes->status,
            'test'=> (string)$quotes->test,
            'company_status'=> (string)$quotes->company_status,
            'price'=> (string)$quotes->price,
            'description'=> (string)$quotes->description,
            'adenda'=> (string)$quotes->adenda,
            'created_at'=> (string)$quotes->created_at,
            'updated_at'=> (string)$quotes->updated_at,
            'date'=> (string)$quotes->date,
            'hour'=> (string)$quotes->hour,
            'user'=> $userTransform->transform($quotes->user),
            'QuotesVersionLast'=> $quotes->quotesVersionLast(),
            'QuotesList'=> $quotes->quotesVersion,
            'QuotesVersionLastPublish'=> $quotes->quotesVersionLastPublish(),
            'QuotestatusUser'=> $quotes->quoteStatusUser(),
            'slugQuote'     => (string)$quotes->company->slug,
            'QuotesVersionCount'=> count($quotes->quotesVersion),
            'QuoteType'=> $quotes->type,
            // 'commissionedUsers'=> $quotes->commissionedUsers()
            'participatingUsers'=> $quotes->participatingUsers()
        ];
    }

    public function transformDetail(Quotes $quotes)
    {
        $userTransform = new UserTransformer();
        $companyTransformer = new CompanyTransformer();
        $projectsTransformer = new ProjectsTransformer();

        return [
            //
            'id' => (int)$quotes->id,
            'user_id'=> (int)$quotes->user_id,
            'company_id'=> (int)$quotes->company_id,
            'company' => $companyTransformer->transform($quotes->company),
            'project_id'=> (int)$quotes->project_id,
            'project'=> $projectsTransformer->transform($quotes->project),
            'categories'=> $quotes->categories,
            'name'=> (string)$quotes->name,
            'status'=> (string)$quotes->status,
            'test'=> (string)$quotes->test,
            'company_status'=> (string)$quotes->company_status,
            'price'=> (string)$quotes->price,
            'description'=> (string)$quotes->description,
            'adenda'=> (string)$quotes->adenda,
            'created_at'=> (string)$quotes->created_at,
            'updated_at'=> (string)$quotes->updated_at,
            'date'=> (string)$quotes->date,
            'hour'=> (string)$quotes->hour,
            'user'=> $userTransform->transform($quotes->user),
            'QuotesVersionLast'=> $quotes->quotesVersionLast(),
            'QuotesVersionLastPublish'=> $quotes->quotesVersionLastPublish(),
            'QuotesVersionCount'=> count($quotes->quotesVersion),
            'QuotesVersionList'=> $quotes->quotesVersion->sortBy([ ['created_at', 'desc'] ]),
            'QuotestatusUser'=> $quotes->quoteStatusUser(),
            'slugQuote'     => (string)$quotes->company->slug,
            'QuoteType'=> (string)$quotes->type,
            // 'commissionedUsers'=> $quotes->commissionedUsers()
            'participatingUsers'=> $quotes->participatingUsers()
        ];
    }
}
