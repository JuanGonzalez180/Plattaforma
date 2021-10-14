<?php

namespace App\Http\Controllers\WebControllers\scripts;

use App\Models\Files;
use App\Models\Blog;
use App\Models\Company;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UpdateFilesSizeController extends Controller
{
    public $routeFile       = 'storage/';

    public function index()
    {
        $files  = Files::whereNull('size')->get();
        $images = Image::whereNull('size')->get();

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

    public function bitesToMegaBites($file_size)
    {
        // return round((filesize($file_size) / pow(1024, 2)), 5);
        return filesize($file_size);
    }
}
