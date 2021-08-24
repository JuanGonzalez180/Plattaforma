<?php

namespace App\Http\Controllers\WebControllers\product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;

class ProductController extends Controller
{
    public function index($id)
    {
        $products   = Products::where('company_id',$id)
                ->orderBy('name','asc')
                ->get();

        return view('product.index', compact('products'));
    }

    public function show($id)
    {
        $product    = Products::find($id);

        return view('product.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Products::find($id);
        return view('product.edit',compact('product'));
    }

    public function update(Request $request, $id)
    {

        return view('product.show', compact('product'));
    }
}
