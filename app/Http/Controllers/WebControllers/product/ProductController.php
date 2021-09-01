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

        $status     = [
            Products::PRODUCT_ERASER,
            Products::PRODUCT_PUBLISH
        ];

        return view('product.show', compact(['product','status']));
    }

    public function edit($id)
    {
        $product    = Products::find($id);

        return view('product.edit',compact('product'));
    }

    public function update(Request $request)
    {
        $product = Products::find($request->id);
        $product->status = $request->status;
        $product->save();

        $message = "Se ha modificado el estado con exito";
        switch ($request->status) {
            case Products::PRODUCT_ERASER:
                //
                break;
            case Products::PRODUCT_PUBLISH:
                //
                break;
        };

        return response()->json(['message' => $message], 200);
    }
}
