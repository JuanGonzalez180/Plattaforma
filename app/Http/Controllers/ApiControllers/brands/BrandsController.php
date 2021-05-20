<?php

namespace App\Http\Controllers\ApiControllers\brands;

use JWTAuth;
use App\Models\User;
use App\Models\Brands;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;
use Illuminate\Http\Request;


class BrandsController extends ApiController
{

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index()
    {
        $brands = Brands::where('status',Brands::BRAND_ENABLED)
            ->orderBy('name', 'ASC')
            ->get();

        return $this->showAllPaginate($brands);
    }

    public function store(Request $request)
    {
        $user = $this->validateUser();
        $statusValue = ($user->admin == 'true') ? Brands::BRAND_ENABLED : Brands::BRAND_DISABLED;

        $rules = [
            'name' => 'required|unique:brands'
        ];

        $this->validate( $request, $rules );

        // Iniciar Transacción
        DB::beginTransaction();

        //Datos
        $brandFields = $request->all();
        $brandFields['user_id'] = $user->id;
        $brandFields['name'] = ucwords($request->name);
        $brandFields['status'] = $statusValue ;

        try{
            //Crear Marca
            $brand = Brands::create( $brandFields );
        }catch(\Throwable $th){
            $errorBrand = true;
            DB::rollBack();
            $productError = [ 'product' => 'Error, no se ha podido crear la marca' ];
            return $this->errorResponse( $errorBrand, 500 );
        }

        DB::commit();

        return $this->showOne($brand,201);

    }

    public function edit($id)
    {
        $brands = Brands::findOrFail($id);
        $brands->name;
        $brands->status;
        return $this->showOne($brands,200);
    }

    public function update(Request $request, $id)
    {
        $brands = Brands::findOrFail($id);

        if(ucwords($brands['name']) != ucwords($request->name)){
            $rules = [
                'name' => 'required|unique:brands'
            ];

            $this->validate( $request, $rules );
        }

        $brands['name'] = ucwords($request->name);
        $brands['status'] = $request->status;
        $brands->save();

        return $this->showOne($brands,200);
    }

    public function show($id)
    {
        $brands = Brands::find($id);
        return $brands;
    }

    public function destroy($id)
    {
        $brand = Brands::find($id);
        $status = ($brand->status == Brands::BRAND_ENABLED) ? Brands::BRAND_DISABLED : Brands::BRAND_ENABLED;
        $brand->status = $status;
        $brand->save();

        return $this->showOne($brand,200);
    }

    public function showsearch($name)
    {
        $brands = Brands::where('status',Brands::BRAND_ENABLED)
            ->where(strtolower('name'),'LIKE',strtolower($name).'%')
            ->orderBy('name', 'ASC')
            ->get();

        return $this->showAllPaginate($brands);
    }

    public function showBrandToProducts($id)
    {
        $products = Products::where('brand_id',$id)
            ->orderBy('name','ASC')
            ->get();   

        return $this->showAllPaginate($products);
    }

}
