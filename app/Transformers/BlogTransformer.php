<?php

namespace App\Transformers;

use App\Models\Blog;
use App\Transformers\UserTransformer;
use App\Transformers\CompanyTransformer;
use League\Fractal\TransformerAbstract;

class BlogTransformer extends TransformerAbstract
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
    public function transform(Blog $blog)
    {
        $userTransform = new UserTransformer();
        $companyTransform = new CompanyTransformer();

        return [
            'id' => (int)$blog->id,
            'name' => (string)$blog->name,
            'description_short' => (string)$blog->description_short,
            'description' => (string)$blog->description,
            'status' => (string)$blog->status,
            'user_id' => (int)$blog->user_id,
            'company_id' => (int)$blog->company_id,
            'image'=> $blog->image,
            'files'=> $blog->files,
            'user'=> $blog->user,
            'user'=> $userTransform->transform($blog->user),
            'created_at'=> (string)$blog->created_at,
            'updated_at'=> (string)$blog->updated_at
        ];
    }

    public function transformDetail(Blog $blog)
    {
        $userTransform = new UserTransformer();
        $companyTransform = new CompanyTransformer();

        return [
            'id' => (int)$blog->id,
            'name' => (string)$blog->name,
            'description_short' => (string)$blog->description_short,
            'description' => (string)$blog->description,
            'status' => (string)$blog->status,
            'user_id' => (int)$blog->user_id,
            'company_id' => (int)$blog->company_id,
            'image'=> $blog->image,
            'user'=> $blog->user,
            'user'=> $userTransform->transform($blog->user),
            'company'=> $companyTransform->transform($blog->company),
            'created_at'=> (string)$blog->created_at,
            'updated_at'=> (string)$blog->updated_at
        ];
    }
}
