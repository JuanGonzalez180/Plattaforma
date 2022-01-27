<?php

namespace App\Http\Controllers\WebControllers\uploadfile\template;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductFileController extends Controller
{
    public $routeFile           = 'public/';
    public $routeFileTemplate   = 'template/product_csv/';
    public $nameFile = "template_product";

    public function index()
    {
        $fileName = $this->nameFile . ".xlsx";
        $existFile = false;
        $routeFileFull = '/storage/' . $this->routeFileTemplate . $fileName;
        if(Storage::disk('public')->exists( $this->routeFileTemplate . $fileName )){
            $existFile = true;
        }
        return view('uploadfile.productfile.index', compact( 'fileName', 'existFile', 'routeFileFull' ) );
    }

    public function store(Request $request)
    {
        $rules = [
            'template' => 'required|mimes:xlsx'
        ];

        $this->validate( $request, $rules );

        $fileName = $this->nameFile.'.'.$request->template->getClientOriginalExtension();

        $request->template->storeAs( $this->routeFile.$this->routeFileTemplate, $fileName);

        return redirect()->route('template-product-file.index')->with('success', 'El archivo se ha subido con exito');
    }
}
