<?php

namespace App\Http\Controllers\WebControllers\uploadfile\template;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductFileController extends Controller
{
    public $routeFile           = 'public/';
    public $routeFileTemplate   = 'template/product_csv/';


    public function index()
    {
        return view('uploadfile.productfile.index');
    }

    public function store(Request $request)
    {
        $rules = [
            'template' => 'required|mimes:xlsx'
        ];

        $this->validate( $request, $rules );

        $fileName = 'template_product_csv'.'.'.$request->template->extension();

        $request->template->storeAs( $this->routeFile.$this->routeFileTemplate, $fileName);

        return redirect()->route('template-product-file.index')->with('success', 'El archivo se ha subido con exito');
    }
}
