<?php

namespace App\Http\Controllers\WebControllers\product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;

class ProductController extends Controller
{
    public function indexType($type, $id)
    {
        if($type == 'product')
        {
            $title      = 'Productos';
            $products   = Products::where('company_id',$id)
                    ->where('type', Products::TYPE_PRODUCT)
                    ->orderBy('name','asc')
                    ->get();
        }
        else
        {
            $title      = 'Servicios';
            $products   = Products::where('company_id',$id)
                    ->where('type', Products::TYPE_SERVICE)
                    ->orderBy('name','asc')
                    ->get();
        }

        return view('product.index', compact('title','products'));
    }

    public function show($id)
    {
        $product    = Products::find($id);

        return view('product.show', compact('product'));
    }
}
