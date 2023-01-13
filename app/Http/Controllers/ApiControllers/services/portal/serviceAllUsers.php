<?php

namespace App\Http\Controllers\ApiControllers\services\portal;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class serviceAllUsers extends ApiController
{

    public function __invoke(Request $request)
    {
        $usersArray = [];
        $companies = $this->getAllCompanies();

        foreach($companies as $company)
        {
            $usersArray = array_merge($usersArray, $company->userIds());
        }

        $users = $this->getUsers($usersArray);

        $userList = $this->setAllUsers($users);

        return $userList;
    }

    public function setAllUsers($users)
    {
        $usersArray = [];
        foreach($users as $user)
        {
            $value['avatar']            = isset($user->companyFull()->image) ? $user->companyFull()->image->url : "0";
            $value['name']              = $user->name;
            $value['uid']               = $user->id;
            $value['metadata']          = $this->getMetdata($user);

            $usersArray[] = $value;

        }

        return $usersArray;
    }

    public function getMetdata($user)
    {
        $value['rawMetadata'] = $user->companyFull()->name;
        $value['email'] = $user->email;
        $value['img_user'] = isset($user->image) ? $user->image->url : "0";
        $value['id_company'] = $user->companyFull()->id;

        return $value;
    }

    public function getAllCompanies()
    {
        return Company::where('status',Company::COMPANY_APPROVED)->get();
    }

    public function getUsers($userArray)
    {
        return User::whereIn('id', $userArray)->orderBy('id','asc')->skip(0)->take(20)->get();
    }
}
