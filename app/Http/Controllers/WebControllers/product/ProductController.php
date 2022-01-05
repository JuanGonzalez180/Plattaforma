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
        $products       = Products::where('company_id', $company_id)->orderBy('id', 'desc');

        // $products       = $products->get();


        // foreach ($products as $product) {
        //     $product['size_product'] = $product->fileSizeProduct();
        // }

        // $products = collect($products);

        return DataTables::of($products)
            ->editColumn('company_id', function (Products $value) {
                return $value->company->name;
            })
            ->editColumn('user_id', function (Products $value) {
                return $value->user->nameResponsable();
            })
            ->editColumn('brand_id', function (Products $value) {
                return $value->brand->name;
            })
            ->addColumn('size_product', function (Products $value) {
                return "<span class='badge badge-primary' style='width: 100%;'>" . $this->formatSize($value->fileSizeProduct()) . "</span>";
                // return $value->fileSizeProduct();
            })
            ->addColumn('actions', 'product.datatables.action')
            ->rawColumns(['actions', 'user_id', 'size_product'])
            ->orderColumn('size_product', function ($query, $order) {
                $query->orderBy('status', 'asc');
            })
            ->toJson();
    }

    public function formatSize($file_size)
    {
        if (round(($file_size / pow(1024, 2)), 3) < '1') {
            $file = round(($file_size*0.00097426203), 1). ' KB';
        } else if (round(($file_size / pow(1024, 2)), 1) < '1024') {
            $file = round(($file_size / pow(1024, 2)), 1) . ' MB';
        } else if (round(($file_size / pow(1024, 2)), 1) >= '1024') {
            $file = round(($file_size / pow(1024, 2)), 1) . ' GB';
        }

        return $file;
    }
}
