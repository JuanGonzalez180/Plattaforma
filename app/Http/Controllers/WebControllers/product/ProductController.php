<?php

namespace App\Http\Controllers\WebControllers\product;

use DataTables;
use App\Models\Products;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index($company_id)
    {
        return view('product.index', compact('company_id'));
    }

    public function show($id)
    {
        $product    = Products::find($id);

        $status     = [
            Products::PRODUCT_ERASER,
            Products::PRODUCT_PUBLISH
        ];

        return view('product.show', compact(['product', 'status']));
    }

    public function edit($id)
    {
        $product    = Products::find($id);

        return view('product.edit', compact('product'));
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

    public function getCompanyProducts(Request $request)
    {

        $company_id     = $request->company_id;
        $products       = Products::where('company_id', $company_id)->orderBy('id', 'asc');

        return DataTables::of($products)
            ->editColumn('company_id', function (Products $value) {
                return $value->company->name;
            })
            ->editColumn('user_id', function (Products $value) {
                return $value->user->name;
            })
            ->editColumn('brand_id', function (Products $value) {
                return $value->brand->name;
            })
            ->editColumn('status', function (Products $value) {
                return $value->brand->name;
                
            })
            ->addColumn('actions', '<button>hola</button>')
            ->rawColumns(['actions'])
            ->toJson();
    }
}
