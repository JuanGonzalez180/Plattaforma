<?php

namespace App\Http\Controllers\ApiControllers\products;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class ProductsController extends ApiController
{
    public $routeFile = 'public/';
    public $routeProducts = 'images/products/';

    public function validateUser(){
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
    public function index()
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $companyID = $user->companyId();
        if( $companyID && $user->userType() == 'oferta' ){
            $productsAdmin = Products::where('company_id', $companyID)->get();
            foreach( $productsAdmin as $key => $product ){
                $product->user;
                $product->user['url'] = $product->user->image ? url( 'storage/' . $product->user->image->url ) : null;
            }

            return $this->showAllPaginate($productsAdmin);
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

        $this->validate( $request, $rules );
        
        // Iniciar Transacción
        DB::beginTransaction();

        // Datos
        $productFields['name'] = $request['name'];
        $productFields['user_id'] = $request['user'] ? $request['user'] : $user->id;
        $productFields['company_id'] = $user->companyId();
        $productFields['description'] = $request['description'];
        $productFields['type'] = $request['type'];

        try{
            // Crear Producto
            $product = Products::create( $productFields );
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorProduct = true;
            DB::rollBack();
            $productError = [ 'product' => 'Error, no se ha podido crear el producto' ];
            return $this->errorResponse( $productError, 500 );
        }

        if( $product ){
            if( $request->categories && $request->type == 'producto' ){
                foreach ($request->categories as $key => $categoryId) {
                    $product->productCategories()->attach($categoryId);
                }
            }

            if( $request->categoriesServices && $request->type == 'servicio' ){
                foreach ($request->categoriesServices as $key => $categoryId) {
                    $product->productCategoryServices()->attach($categoryId);
                }
            }

            if( $request->image ){
                $png_url = "product-".time().".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",")+1);
                $data = base64_decode($img);
                
                $routeFile = $this->routeProducts.$product->id.'/'.$png_url;
                Storage::disk('local')->put( $this->routeFile . $routeFile, $data);
                $product->image()->create(['url' => $routeFile]);
            }
        }

        DB::commit();

        return $this->showOne($product,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
