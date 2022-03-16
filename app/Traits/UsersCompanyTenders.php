<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

trait UsersCompanyTenders
{
    public function getTeamsCompanyUsers($company,$value)
    {
        $teams =  Team::where('teams.company_id', $company->id)
            ->where('teams.status', Team::TEAM_APPROVED)
            ->join('users', 'users.id','=', 'teams.user_id');

        if($value == 'id')
        {
            $teams = $teams->pluck('users.id')
                ->all();

            return array_merge([$company->user->id], $teams);
        }
        if($value == 'email')
        {
            $teams = $teams->pluck('users.email')
                ->all();

            return array_merge([$company->user->email], $teams);
        }
    }

}