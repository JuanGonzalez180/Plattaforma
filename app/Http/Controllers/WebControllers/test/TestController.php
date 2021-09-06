<?php

namespace App\Http\Controllers\WebControllers\test;

use App\Models\Brands;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use TaylorNetwork\UsernameGenerator\Generator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TestController extends Controller
{
    public $routeFile       = 'public/';

    public $routeFileBD     = 'images/temp/';
    public $routeProducts   = 'images/products/';
    
    public function index()
    {
        return view('test.index');
    }

    public function show()
    {

        

    }

    public function createTemporaryTable()
    {
        Schema::create('temp_product_img', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('product_id')->unsigned();
            $table->string('img', 1000);
            $table->string('status')->default('false');
            //$table->temporary();
        });
    }

    public function store(Request $request)
    {
        if(!Schema::hasTable('temp_product_img')){
            $this->createTemporaryTable();
        };

        $generator = new Generator();
        if( $request->file_cvs ){
            $fileName = uniqid().'.'.$request->file_cvs->extension();
            $request->file_cvs->storeAs( $this->routeFile.$this->routeFileBD, $fileName);

            $file_cvs = 'storage/'.$this->routeFileBD.$fileName;            

            $file_handle = fopen($file_cvs, 'r');

            $lineNumber = 1;

            while (($raw_string = fgets($file_handle)) !== false) {

                $row = str_getcsv($raw_string);
                $this->createProduct($row);

                // Aumentar la línea actual
                $lineNumber++;
            }

            fclose($file_handle);
            die;
        }
        
    }

    public function createProduct($row)
    {
        $brand_id = $this->getBrandId($row[2]);

        $product = Products::where(strtoupper('name'), strtoupper($row[1]))
            ->where('brand_id',$brand_id);

        if(!$product->exists())
        {
            $product = new Products;
            $product->name        = ucfirst($row[1]);
            $product->company_id  = 1;
            $product->user_id     = 1;
            $product->brand_id    = $brand_id;
            $product->description = $row[3];
            $product->type        = ucfirst($row[4]);
            $product->status      = Products::PRODUCT_PUBLISH;
            $product->save();

            DB::table('temp_product_img')->insert([
                'product_id' => $product->id,
                'img'        => $row[5]
            ]);

        }
    }

    public function getBrandId($name)
    {
        $brand = Brands::where(strtoupper('name'), strtoupper($name));

        if($brand->exists())
        {
            $brand = $brand->first();
        }
        else
        {
            $brand = new Brands;
            $brand->user_id     = 1;
            $brand->company_id  = 0;
            $brand->name        = ucfirst($name);
            $brand->save();
        }

        return $brand->id;
    }

}
