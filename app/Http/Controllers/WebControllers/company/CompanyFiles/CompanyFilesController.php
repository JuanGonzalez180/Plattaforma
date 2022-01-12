<?php

namespace App\Http\Controllers\WebControllers\company\CompanyFiles;

use DataTables;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CompanyFilesController extends Controller
{
    public function getFiles(Request $request)
    {
        $files  = Company::find($request->company_id);


        switch ($request->model) {
            case 'products':
                $files  = $files->fileListProduct();
                break;
            case 'brands':
                $files  = $files->fileListBrands();
                break;
            case 'projects':
                $files  = $files->fileListProject();
                break;
            case 'tenders':
                $files  = $files->fileListTender();
                break;
            case 'blogs':
                $files  = $files->fileListBlogs();
                break;
            case 'portfolios':
                $files  = $files->fileListPortfolio();
                break;
            case 'catalogs':
                $files  = $files->fileListCatalog();
                break;
            case 'advertisings':
                $files  = $files->fileListAdvertising();
                break;
            case 'all':
                $files  = $files->fileListTotal();
                break;
        }

        $files = $files->sortBy([['updated_at', 'desc']]);



        return DataTables::of($files)
            ->editColumn('url', function ($value) {
                $file = explode("/", $value->url);
                return '<a href="' . url('storage/' . $value->url) . '" target="_blank"><i class="far fa-file-alt"></i> ' . end($file) . '</a><br><b>Tamaño | </b><span class="badge badge-primary">'.$this->formatSize($value->size).'</span>';
            })
            ->editColumn('updated_at', function ($value) {
                $date = $value->updated_at;
                // $date = date('g:i a', strtotime($value->updated_at));
                return $date;
            })
            ->rawColumns(['url','updated_at'])
            ->toJson();
    }

    public function formatSize($file_size)
    {
        if (round(($file_size / pow(1024, 2)), 1) < '1') {
            $file = round(($file_size*0.00097426203), 1). ' KB';
        } else if (round(($file_size / pow(1024, 2)), 1) < '1024') {
            $file = round(($file_size / pow(1024, 2)), 1) . ' MB';
        } else if (round(($file_size / pow(1024, 2)), 1) >= '1024') {
            $file = round(($file_size / pow(1024, 2)), 1) . ' GB';
        }

        return $file;
    }
}
