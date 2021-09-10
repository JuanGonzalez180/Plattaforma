<?php

namespace App\Http\Controllers\ApiControllers\uploadfile\csv\productfile;

use JWTAuth;
use App\Models\Brands;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;
use TaylorNetwork\UsernameGenerator\Generator;
use App\Http\Controllers\ApiControllers\ApiController;

class ProductFileController extends ApiController
{
    //rutas
    public $routeFile       = 'public/';
    public $routeFileBD     = 'temp/';
    public $routeProducts   = 'images/products/';

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
            $table->longText('categories');
            $table->longText('tags');
            $table->longText('main_img');
            $table->longText('galery_img');
            $table->longText('files');
            $table->string('status')->default('false');
        });
    }

    public function store(Request $request)
    {
        $rules = [
            'file_csv' => 'required|mimes:csv,txt'
        ];

        $this->validate( $request, $rules );

        if(!Schema::hasTable('temp_product_files')){
            $this->createTemporaryTable();
        };

        $generator = new Generator();

        $fileName = uniqid().'.'.$request->file_csv->extension();
        $request->file_csv->storeAs( $this->routeFile.$this->routeFileBD, $fileName);

        $file_cvs       = 'storage/'.$this->routeFileBD.$fileName;   
                 
        $file_handle    = fopen($file_cvs, 'r');

        $lineNumber = 1;
        while (($raw_string = fgets($file_handle)) !== false)
        {
            if($lineNumber == 1)
            {
                $lineNumber++;
                continue;
            }
            $row = str_getcsv($raw_string);
            $this->createProduct($row);
            // Aumentar la línea actual
            $lineNumber++;
        }

        fclose($file_handle);

        //elimina el archivo csv despues de haber sido leido
        unlink($file_cvs);

        return $this->showOneData( ['success' => 'se han cargado todos los productos correctamente', 'code' => 200 ], 200);
    }

    public function createProduct($row)
    {
        $user       = $this->validateUser();
        $companyID  = $user->companyId();
        $brand_id   = (!empty($row[1])) ? $this->getBrandId($row[1]) : 1;
        $characters = array("{", "}"); 

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

            DB::table('temp_product_files')->insert([
                'product_id'    => $product->id,
                'categories'    => str_replace($characters, "", $row[3]),
                'tags'          => trim(str_replace($characters, "", $row[4])),
                'main_img'      => $row[5],
                'galery_img'    => trim(str_replace($characters, "", $row[6])),
                'files'         => trim(str_replace($characters, "", $row[7]))
            ]);
        }
    }

    public function getBrandId($name)
    {
        $user       = $this->validateUser();
        $companyID  = $user->companyId();

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
        $routeFileTemplate   = 'template/product_csv/';
        $fileName   = 'template_product_csv.xlsx';
        $pathtoFile = $this->routeFile.$routeFileTemplate.$fileName;

        

        return response()->download($pathtoFile);
    }

}
