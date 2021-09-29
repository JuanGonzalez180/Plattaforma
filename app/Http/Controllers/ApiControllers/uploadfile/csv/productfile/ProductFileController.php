<?php

namespace App\Http\Controllers\ApiControllers\uploadfile\csv\productfile;

use JWTAuth;
use App\Models\Brands;
use App\Models\Category;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;
use TaylorNetwork\UsernameGenerator\Generator;
use App\Http\Controllers\ApiControllers\ApiController;
use App\Imports\ProductsImport;

class ProductFileController extends ApiController
{
    //rutas
    public $routeFile       = 'public/';
    public $routeFileBD     = 'temp/';
    public $routeProducts   = 'images/products/';

    public $routeFileTemplate   = 'template/product_csv/';
    public $nameFile = "template_product";

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function createTemporaryTable()
    {
        Schema::create('temp_product_files', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('product_id')->unsigned();
            $table->longText('main_img')->nullable();
            $table->longText('galery_img')->nullable();
            $table->longText('files')->nullable();
            $table->string('status')->default('false')->nullable();
        });
    }

    public function store(Request $request)
    {
        $rules = [
            'file_csv' => 'required|mimes:csv,txt,xls,xlsx'
        ];

        $this->validate( $request, $rules );

        if(!Schema::hasTable('temp_product_files')){
            $this->createTemporaryTable();
        };

        $generator = new Generator();

        $fileName = uniqid().'.'.$request->file_csv->getClientOriginalExtension();
        $request->file_csv->storeAs( $this->routeFile.$this->routeFileBD, $fileName);
        $file_cvs       = 'storage/'.$this->routeFileBD.$fileName;

        // print $file_cvs;
        $user       = $this->validateUser();
        $companyID  = $user->companyId();

        $data = Excel::toArray(new ProductsImport, $file_cvs);
        if( count($data) ){
            foreach ($data[0] as $key => $row) {
                if($key)
                    $this->createProduct($row, $user, $companyID);
            }
        }
        unlink($file_cvs);

        return $this->showOneData( ['success' => 'se han cargado todos los productos correctamente'], 200);
    }

    public function createProduct($row, $user, $companyID)
    {
        $brand_id   = (!empty($row[1])) ? $this->getBrandId($row[1],$user, $companyID) : 1;

        $product = Products::where(strtoupper('name'), strtoupper($row[0]))
            ->where('brand_id',$brand_id);

        if(!$product->exists())
        {
            $product = new Products;
            $product->name        = ucfirst($row[0]);
            $product->company_id  = $companyID;
            $product->user_id     = $user->id;
            $product->brand_id    = $brand_id;
            $product->description = $row[2];
            $product->type        = Products::TYPE_PRODUCT;
            $product->status      = $row[8];
            $product->save();

            //category/categorias
            if(!empty($row[3]))
            $this->addCategories($row[3], $product);

            //tags/Etiquetas
            if(!empty(trim($row[4])))
                $this->addTags(trim($row[4]), $product);

            DB::table('temp_product_files')->insert([
                'product_id'    => $product->id,
                'main_img'      => $row[5],
                'galery_img'    => trim($row[6]),
                'files'         => trim($row[7])
            ]);
        }
    }

    public function getBrandId($name, $user, $companyID)
    {
        $brand = Brands::where(strtoupper('name'), strtoupper($name));

        if($brand->exists())
        {
            $brand = $brand->first();
        }
        else
        {
            $brand = new Brands;
            $brand->user_id     = $user->id;
            $brand->company_id  = $companyID;
            $brand->name        = ucfirst($name);
            $brand->status      = Brands::BRAND_ENABLED;
            $brand->save();
        }

        return $brand->id;
    }

    public function downloadTemplate()
    {
        $fileName   = $this->nameFile.'.xlsx';
        $nameNoCache = uniqid();
        $pathtoFile = url( 'storage/' . $this->routeFileTemplate.$fileName . '?nocache=' . $nameNoCache );

        return $this->showOneData( ['url' => $pathtoFile, 'code' => 200 ], 200);
    }

    /*{
        $categories = array_unique($this->stringToArrayHashtag($categories));

        foreach($categories as $categoryId)
        {
            if(Category::where('id',$categoryId)->exists())
                $product->productCategories()->attach($categoryId);
        }
    }*/

    public function addCategories($categories, $product)
    {
        $categories = array_unique($this->stringToArrayHashtag($categories));

        foreach($categories as $categoryName)
        {   
            $category = Category::where(
                $this->remove_accents(strtolower('name')),
                $this->remove_accents(strtolower($categoryName))
            )->first();

            if($category)
                $product->productCategories()->attach($category);
        }
    }

    public function addTags($tags, $product)
    {
        $tags = array_unique($this->stringToArrayHashtag($tags));

        foreach($tags as $tag)
        {
            $product->tags()->create(['name' => ucfirst($tag)]);
        }
    }

    public function stringToArrayHashtag($string)
    {
        $array = explode("#", $string);
        return $array;
    }

    function remove_accents($array)
    {
        $not_allowed    = array("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
        $allowed        = array("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");

        return str_replace($not_allowed, $allowed ,$array);
    }

}
