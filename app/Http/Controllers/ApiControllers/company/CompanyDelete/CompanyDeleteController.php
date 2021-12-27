<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyDelete;

use App\Models\Blog;
use App\Models\Image;
use App\Models\Files;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyDeleteController extends ApiController
{
    public $routeFile = 'public/';

    public function __invoke($id)
    {
        $company = Company::find($id);

        // $email_message = $this->emailMessageInfo($company);

        //blogs
        $blogs = $company->blogs->pluck('id');
        $this->deleteCompanyBlogs($blogs);
    }

    public function deleteCompanyBlogs($blog_ids)
    {
        $this->deleteFiles($blog_ids, Blog::class);
        $this->deleteImage($blog_ids, Blog::class);
        
        Blog::whereIn('id', $blog_ids)
            ->delete();
    }

    public function deleteCompanyPortFolio($portfolio_ids)
    {

    }

    public function deleteFiles($array_id, $classModel)
    {
        $files  = Files::whereIn('filesable_id', $array_id)
            ->where('filesable_type', $classModel)
            ->get();

        foreach ($files as $file) {
            Storage::disk('local')->delete($this->routeFile . $file->url);
            $file->delete();
        }
    }

    public function deleteImage($array_id, $classModel)
    {
        $images  = Image::whereIn('imageable_id', $array_id)
            ->where('imageable_type', $classModel)
            ->get();

        foreach ($images as $image) {
            Storage::disk('local')->delete($this->routeFile . $image->url);
            //elimina la imagen
            Image::where('imageable_id', $image->imageable_id)
            ->where('imageable_type', $image->imageable_type)
            ->delete();
        }
    }

    public function emailMessageInfo($company)
    {
        return 0;
    }
}
