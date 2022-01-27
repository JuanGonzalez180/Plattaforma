<?php

namespace App\Http\Controllers\WebControllers\scripts;

use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RemoveUnwantedUsersController extends Controller
{
    public function __invoke()
    {
        $users_admin = $this->getUsersAdmin();
        $users_teams = $this->getUsersTeams();

        //usuarios existentes
        $users_exist = array_unique(Arr::collapse([
            $users_admin,
            $users_teams
        ]));

        $user_exclude = $this->getUsersExclude($users_exist);

        // excluye el administrador
        $user_exclude = array_filter($user_exclude->toArray(), function ($value) {
            return $value != 1;
        });

        User::destroy(collect($user_exclude));
    }

    public function getUsersAdmin()
    {
        return User::join('companies', 'companies.user_id', '=', 'users.id')
            ->orderBy('users.id', 'asc')
            ->pluck('users.id');
    }

    public function getUsersTeams()
    {
        return Team::orderBy('user_id', 'asc')
            ->pluck('user_id');
    }

    public function getUsersExclude($users)
    {
        return User::whereNotIn('id', $users)
            ->pluck('id');
    }
}
