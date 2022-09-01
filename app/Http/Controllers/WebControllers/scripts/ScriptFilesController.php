<?php

namespace App\Http\Controllers\WebControllers\scripts;

use App\Models\Files;
use App\Models\Image;
use App\Models\Tenders;
use App\Models\TendersCompanies;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Newsletter;
use PhpParser\Node\Stmt\Foreach_;

class ScriptFilesController extends Controller
{
    public $routeFile       = 'storage/';


    public function genericScript()
    {
        // $tendersCompanies = TendersCompanies::all();
        // foreach ($tendersCompanies as $value)
        // {
        //     $value->user_company_id = $value->company->user->id;
        //     $value->save();
        // }

        // Newsletter::subscribe('davidmejia-2000@outlook.com');

        // if ( ! Newsletter::isSubscribed('davidmejia-2000@outlook.com') ) {
        //     Newsletter::subscribe('davidmejia-2000@outlook.com');
        //     // Newsletter::addTags(['ingeniero', 'sistemas'], 'davidmejia-2000@outlook.com');
        // }


        var_dump('hola mundo');
    }

    public function updateSizeFiles()
    {
        $files  = Files::all();
        $images = Image::all();

        foreach ($variable as $key => $value) {
            # code...
        }

        foreach ($files as $file) {
            if (file_exists($this->routeFile . $file->url)) {
                $file->size = $this->bitesToMegaBites($this->routeFile . $file->url);
                $file->save();
            };
        }

        foreach ($images as $image) {
            if (file_exists($this->routeFile . $image->url)) {
                Image::where('url', $image->url)
                    ->where('imageable_id', $image->imageable_id)
                    ->where('imageable_type', $image->imageable_type)
                    ->where('created_at', $image->created_at)
                    ->where('updated_at', $image->updated_at)
                    ->update(['size' => $this->bitesToMegaBites($this->routeFile . $image->url)]);
            };
        }
    }

    public function deleteFileNotExist()
    {
        $files  = Files::all();
        $images = Image::all();

        foreach ($files as $file) {
            if (!file_exists($this->routeFile . $file->url)) {
                $file->delete();
            };
        }

        foreach ($images as $image) {
            if (!file_exists($this->routeFile . $image->url)) {
                Image::where('url', $image->url)
                    ->where('imageable_id', $image->imageable_id)
                    ->where('imageable_type', $image->imageable_type)
                    ->where('created_at', $image->created_at)
                    ->where('updated_at', $image->updated_at)
                    ->delete();
            };
        }
    }

    public function bitesToMegaBites($file_size)
    {
        return filesize($file_size);
    }
}
