<?php

namespace App\Http\Controllers\ApiControllers\products;

use JWTAuth;
use App\Models\Tags;
use App\Models\Image;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class ProductsController extends ApiController
{
    public $routeFile = 'public/';
    public $routeProducts = 'images/products/';

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $companyID = $user->companyId();
        if ($companyID && $user->userType() == 'oferta') {
            
            if ($user->isAdminFrontEnd()) {
                // Si es admin
                $products = Products::where('company_id', $companyID)
                    ->orderBy('id', 'desc');
            } else {
                $products = Products::where('company_id', $companyID)
                    ->where('user_id', $user->id)
                    ->orderBy('id', 'desc');
            }

            if( $request->filter ){
                $products = $products->where(strtolower('name'),'LIKE','%'.strtolower($request->filter).'%');
            }

            $products = $products->get();

            return $this->showAllPaginate($products);
        }

        return [];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = $this->validateUser();

        $rules = [
            'name' => 'required',
            'type' => 'required'
        ];

        $this->validate($request, $rules);

        //verifica el estado del usuario
        /*if (!$this->statusCompanyUser($user)) {
            $productError = ['product' => 'Error, El usuario debe pagar la suscripción'];
            return $this->errorResponse($productError, 500);
        };*/

        // Iniciar Transacción
        DB::beginTransaction();

        // Datos
        $productFields['name'] = $request['name'];
        $productFields['code'] = $request['code'];
        $productFields['status'] = $request['status'];
        $productFields['user_id'] = $request['user'] ?? $user->id;
        $productFields['company_id'] = $user->companyId();
        $productFields['brand_id'] = $request['brand'] ?? 1;
        $productFields['description'] = $request['description'];
        $productFields['type'] = $request['type'];

        try {
            // Crear Producto
            $product = Products::create($productFields);
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorProduct = true;
            DB::rollBack();
            $productError = ['product' => 'Error, no se ha podido crear el producto'];
            return $this->errorResponse($productError, 500);
        }

        if ($product) {
            if ($request->categories) {
                foreach ($request->categories as $key => $categoryId) {
                    $product->productCategories()->attach($categoryId);
                }
            }

            if ($request->tags) {
                foreach ($request->tags as $key => $tag) {
                    $product->tags()->create(['name' => $tag['displayValue']]);
                }
            }

            if ($request->categoriesServices && $request->type == 'servicio') {
                foreach ($request->categoriesServices as $key => $categoryId) {
                    $product->productCategoryServices()->attach($categoryId);
                }
            }

            if ($request->image) {

                $png_url    = "product-" . time() . ".jpg";
                $img        = $request->image;
                $img        = substr($img, strpos($img, ",") + 1);
                $data       = base64_decode($img);


                $routeFile = $this->routeProducts . $product->id . '/' . $png_url;
                Storage::disk('local')->put($this->routeFile . $routeFile, $data);

                $product->image()->create(['url' => $routeFile]);
            }
        }

        DB::commit();

        return $this->showOne($product, 201);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $user = $this->validateUser();

        $product = Products::findOrFail($id);
        $product->image;
        $product->productCategories;
        $product->productCategoryServices;
        $product->user;
        $product->user->image;
        $product->brand;
        $product->brand->image;
        $product->tags;
        return $this->showOne($product, 200);
    }

    public function statusCompanyUser($user)
    {
        if ($user->isAdminFrontEnd()) {
            $company = $user->company[0];
        } elseif ($user->team) {
            $company = $user->team->company;
        }

        return $company->companyStatusPayment();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $idProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $rules = [
            'name' => 'required',
            'type' => 'required'
        ];

        $this->validate($request, $rules);

        /*if (!$this->statusCompanyUser($user)) {
            $productError = ['product' => 'Error, El usuario debe pagar la suscripción'];
            return $this->errorResponse($productError, 500);
        };*/

        // Datos
        $product = Products::findOrFail($id);

        // Datos
        $productFields['name'] = $request['name'];
        $productFields['code'] = $request['code'];
        $productFields['status'] = $request['status'];
        $productFields['user_id'] = $request['user'] ?? $user->id;
        $productFields['brand_id'] = $request['brand'] ?? 1;
        $productFields['description'] = $request['description'];
        $productFields['type'] = $request['type'];

        $product->update($productFields);

        // Categorías
        // Eliminar los anteriores
        foreach ($product->productCategories as $key => $category) {
            $product->productCategories()->detach($category->id);
        }

        foreach ($product->tags as $key => $tag) {
            $tag->delete();
        }

        if ($request->categories) {
            foreach ($request->categories as $key => $categoryId) {
                $product->productCategories()->attach($categoryId);
            }
        }

        if ($request->tags) {
            foreach ($request->tags as $key => $tag) {
                $product->tags()->create(['name' => $tag['displayValue']]);
            }
        }

        // Categorías Servicios
        // Eliminar los anteriores
        foreach ($product->productCategoryServices as $key => $category) {
            $product->productCategoryServices()->detach($category->id);
        }

        if ($request->categoriesServices && $request->type == 'servicio') {
            foreach ($request->categoriesServices as $key => $categoryId) {
                $product->productCategoryServices()->attach($categoryId);
            }
        }

        // Imágenes
        if ($request->image) {
            $png_url    = "product-" . time() . ".jpg";
            $img        = $request->image;
            $img        = substr($img, strpos($img, ",") + 1);
            $data       = base64_decode($img);
            $routeFile  = $this->routeProducts . $product->id . '/' . $png_url;

            Storage::disk('local')->put($this->routeFile . $routeFile, $data);

            if ($product->image) {
                Storage::disk('local')->delete($this->routeFile . $product->image->url);
                $product->image()->update(['url' => $routeFile]);
            } else {
                $product->image()->create(['url' => $routeFile]);
            }
        }

        return $this->showOne($product, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $product = Products::findOrFail($id);

        if ($product->image) {
            Storage::disk('local')->delete($this->routeFile . $product->image->url);
            Image::where('imageable_id', $product->id)
                ->where('imageable_type', Products::class)
                ->delete();
        }

        //elimina las ctegorias relacionadas
        foreach ($product->productCategories as $key => $category) {
            $product->productCategories()->detach($category->id);
        }

        foreach ($product->productCategoryServices as $key => $category) {
            $product->productCategoryServices()->detach($category->id);
        }

        //Elimina Etiquetas
        if ($product->tags)
            Tags::destroy($product->tags);

        if ($product->files) {
            foreach ($product->files as $key => $file) {
                Storage::disk('local')->delete($this->routeFile . $file->url);
                $file->delete();
            }
        }

        $product->delete();

        return $this->showOneData(['success' => 'Se ha eliminado correctamente el proyecto', 'code' => 200], 200);
    }
}
