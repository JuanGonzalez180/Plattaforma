<?php

namespace App\Http\Controllers\ApiControllers\search\item;

use JWTAuth;
use App\Models\Tags;
use App\Models\Brands;
use App\Models\Company;
use App\Models\Products;
use App\Models\TypesEntity;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemProductController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function __invoke(Request $request)
    {
        $products = $this->getProductEnabled();

        $search         = !isset($request->search) ? null : $request->search;

        $type_entity    = ($request->type_entity == 'all') ? null : $request->type_entity;

        if (!is_null($type_entity)) {
            $products = $this->getProductsTypeEntity($products, $type_entity);
        }

        if (!is_null($search)) {
            $products = $this->getProductsSearchNameItem($products, $search);
        }

        $products = Products::whereIn('id', $products)
            ->orderBy('name', 'asc')
            ->get();

        return $this->showAllTransformer($products);
    }

    public function getProductsSearchNameItem($products, $search)
    {
        //busca por el producto por el nombre de la compañia
        $productCompanyName     = $this->getProductCompanyName($products, $search);
        //busca por el nombre del producto
        $productName            = $this->getProductName($products, $search);
        //busca por el codigo del producto
        // $productCode            = $this->getProductCode($products, $search);
        //busca por el nombre de las etiquetas del producto
        // $productTags            = $this->getProductTags($products, $search);
        //buscar por la marca del producto
        // $productBrands          = $this->getProductBrand($products, $search);

        $products = array_unique(Arr::collapse([
            $productCompanyName,
            $productName,
            // $productCode,
            // $productTags,
            // $productBrands
        ]));

        return $products;
    }

    public function getProductCompanyName($products, $name)
    {
        return Company::where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->join('products', 'products.company_id', '=', 'companies.id')
            ->where('products.status', Products::PRODUCT_PUBLISH)
            ->whereIn('products.id', $products)
            ->pluck('products.id');
    }

    public function getProductName($products, $name)
    {
        return Products::whereIn('products.id', $products)
            ->where(strtolower('products.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('products.id');
    }

    public function getProductBrand($products, $name)
    {
        return Brands::where(strtolower('brands.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->join('products', 'products.brand_id', '=', 'brands.id')
            ->whereIn('products.id', $products)
            ->pluck('products.id');
    }

    public function getProductTags($products, $name)
    {
        return Tags::where('tags.tagsable_type', Products::class)
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->whereIn('tags.tagsable_id', $products)
            ->join('products', 'products.id', '=', 'tags.tagsable_id')
            ->pluck('products.id');
    }

    public function getProductCode($products, $name)
    {
        return Products::whereIn('products.id', $products)
            ->where(strtolower('products.code'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('products.id');
    }

    public function getProductEnabled()
    {
        return Products::where('products.status', Products::PRODUCT_PUBLISH)
            ->join('images', 'images.imageable_id', '=', 'products.id')
            ->where('images.imageable_type', Products::class)
            ->pluck('products.id');
    }

    public function getProductsTypeEntity($products, $type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->join('products', 'products.company_id', '=', 'companies.id')
            ->whereIn('products.id', $products)
            ->where('products.status', Products::PRODUCT_PUBLISH)
            ->pluck('products.id');
    }
}
