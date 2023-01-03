<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;
use App\Transformers\CompanyTransformer;

class UserTransformer extends TransformerAbstract
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
    public function transform(User $user)
    {
        $companyTransformer = new CompanyTransformer();

        return [
            'id'        => (int)$user->id,
            'name'      => (string)$user->name,
            'lastname'  => (string)$user->lastname,
            'created_at'=> (string)$user->created_at,
            'updated_at'=> (string)$user->updated_at,
            'url'       => (string)$user->image ? url( 'storage/' . $user->image->url ) : '',
            'company'   => ($user->companyFull())? $companyTransformer->transform($user->companyFull()) : null,
        ];
    }

    public function transformCometChat(User $user)
    {
        return [
            'id'                => (int)$user->id,
            'user_name'         => (string)$user->name,
            'user_image'        => (string)$user->image ? url( 'storage/' . $user->image->url ) : null,
            'company_name'      => ($user->companyName())? $user->companyName() : null,
            'company_image'     => ($user->companyImg())? $user->companyImg() : null,
        ];
    }
}
