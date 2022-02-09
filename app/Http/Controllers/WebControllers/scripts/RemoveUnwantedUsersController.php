<?php

namespace App\Http\Controllers\WebControllers\scripts;

use App\Models\User;
use App\Models\Team;
use App\Models\Portfolio;
use App\Models\Catalogs;
use App\Models\Image;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use function Ramsey\Uuid\v1;

class RemoveUnwantedUsersController extends Controller
{
    public $routeFile           = 'public/';
    public $routeCatalogs       = 'images/catalogs/';

    public function __invoke()
    {
        // $users_admin = $this->getUsersAdmin();
        // $users_teams = $this->getUsersTeams();

        // //usuarios existentes
        // $users_exist = array_unique(Arr::collapse([
        //     $users_admin,
        //     $users_teams
        // ]));

        // $user_exclude = $this->getUsersExclude($users_exist);

        // // excluye el administrador
        // $user_exclude = array_filter($user_exclude->toArray(), function ($value) {
        //     return $value != 1;
        // });

        // User::destroy(collect($user_exclude));
        $portfolios = Portfolio::where('company_id',69)->get();

        echo '<pre>';
        foreach($portfolios as $portfolio)
        {
            $catalogFileds['name']              = $portfolio->name;
            $catalogFileds['description_short'] = $portfolio->description_short;
            $catalogFileds['description']       = $portfolio->description;
            $catalogFileds['status']            = $portfolio->status;
            $catalogFileds['user_id']           = $portfolio->user_id;
            $catalogFileds['company_id']        = $portfolio->company_id;

            $catalog = Catalogs::create($catalogFileds);

            $fileName           = explode("/", $portfolio->image->url);
            $newFolderImage     = $this->routeCatalogs.$catalog->id.'/'.$fileName[3];




            Storage::copy($this->routeFile.$portfolio->image->url, $this->routeFile.$newFolderImage);
            Image::create(['url' => $newFolderImage, 'imageable_id' => $catalog->id, 'imageable_type' => Catalogs::class]);

            foreach($portfolio->files as $file)
            {
                $fileName           = $file->name;
                $newFolderFile     = $this->routeCatalogs.$catalog->id.'/documents'.'/'.$fileName;
                Storage::copy($this->routeFile.$file->url, $this->routeFile.$newFolderFile);

                $catalog->files()->create([ 'name' => $fileName, 'type'=> Catalogs::class, 'url' => $newFolderFile]);
            }

        }
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
