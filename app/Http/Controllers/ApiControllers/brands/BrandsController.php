<?php

namespace App\Http\Controllers\ApiControllers\brands;

use JWTAuth;
use App\Models\User;
use App\Models\Brands;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class BrandsController extends ApiController
{
    public $routeFile = 'public/';
    public $routeBrands = 'images/brands/';

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index()
    {
        $user = $this->validateUser();
        $companyId = $user->companyId();

        $brands = Brands::where('company_id', $companyId)
            ->orderBy('name', 'ASC')
            ->get();

        return $this->showAllPaginate($brands);
    }

    public function store(Request $request)
    {
        $user = $this->validateUser();
        $companyId = $user->companyId();

        $statusValue = Brands::BRAND_DISABLED;
        $rules = [
            'name' => 'required|unique:brands'
        ];

        $this->validate($request, $rules);

        // Iniciar Transacción
        DB::beginTransaction();

        //Datos
        $brandFields = $request->all();
        $brandFields['user_id'] = $user->id;
        $brandFields['company_id'] = $companyId;
        $brandFields['name'] = ucwords($request->name);
        $brandFields['status'] = $statusValue;

        try {
            //Crear Marca
            $brand = Brands::create($brandFields);
        } catch (\Throwable $th) {
            $errorBrand = true;
            DB::rollBack();
            $brandError = ['brand' => 'Error, no se ha podido crear la marca'];
            return $this->errorResponse($brandError, 500);
        }

        if ($brand) {
            if ($request->image) {
                $png_url = "brand-" . time() . ".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",") + 1);
                $data = base64_decode($img);

                $routeFile = $this->routeBrands . $brand->id . '/' . $png_url;
                Storage::disk('local')->put($this->routeFile . $routeFile, $data);
                $brand->image()->create(['url' => $routeFile]);
            }
        }

        DB::commit();

        return $this->showOne($brand, 201);
    }

    public function edit($id)
    {
        $brand = Brands::findOrFail($id);
        $brand->image;
        return $this->showOne($brand, 200);
    }

    public function update(Request $request, $id)
    {
        $user = $this->validateUser();
        $companyId = $user->companyId();

        $brand = Brands::findOrFail($id);

        if (ucwords($brand['name']) != ucwords($request->name)) {
            $rules = [
                'name' => 'required|unique:brands'
            ];

            $this->validate($request, $rules);
        }

        $brand['name'] = ucwords($request->name);
        if ($brand->company_id == $companyId) {
            $brand->save();
            // Imágenes
            if ($request->image) {
                $png_url = "brand-" . time() . ".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",") + 1);
                $data = base64_decode($img);
                $routeFile = $this->routeBrands . $brand->id . '/' . $png_url;

                Storage::disk('local')->put($this->routeFile . $routeFile, $data);

                if ($brand->image) {
                    Storage::disk('local')->delete($this->routeFile . $brand->image->url);
                    $brand->image()->update(['url' => $routeFile]);
                } else {
                    $brand->image()->create(['url' => $routeFile]);
                }
            }
        }

        return $this->showOne($brand, 200);
    }

    public function show($id)
    {
        $brands = Brands::find($id);
        return $brands;
    }

    public function destroy($id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();
        $companyId = $user->companyId();

        $brand = Brands::findOrFail($id);
        $countProducts = Products::where('brand_id', $id)
            ->get()
            ->count();

        if ($brand->company_id == $companyId && !$countProducts) {
            if ($brand->image)
                Storage::disk('local')->delete($this->routeFile . $brand->image->url);
            $brand->delete();
        } else {
            $brandError = ['brand' => 'Error, no se ha podido eliminar la marca, ya existen productos asociados'];
            return $this->errorResponse($brandError, 500);
        }

        return $this->showOneData(['success' => 'Se ha eliminado correctamente el proyecto', 'code' => 200], 200);
    }
}
