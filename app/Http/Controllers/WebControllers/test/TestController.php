<?php

namespace App\Http\Controllers\WebControllers\test;

use Image;
use App\Models\Brands;
use App\Models\Products;
use App\Models\Category;
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

    public $routeFileBD     = 'temp/';
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

    public function url_exists($url)
    {
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);

        $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $status = ($code == 200) ? true : false;

        curl_close($ch);
        return $status;
    }

    public function stringToArray($string)
    {
        $array = explode(",", $string);
        return $array;
    }

    public function addCategories($categories, $product)
    {
        $categories = array_unique($this->stringToArray($categories));

        foreach($categories as $categoryId)
        {
            if(Category::where('id',$categoryId)->exists())
                $product->productCategories()->attach($categoryId);
        }
    }

    public function addTags($tags, $product)
    {
        $tags = array_unique($this->stringToArray($tags));

        foreach($tags as $tag)
        {
            $product->tags()->create(['name' => ucfirst($tag)]);
        }
    }

    public function addFiles($files, $product)
    {
        $files = array_unique($this->stringToArray($files));

        foreach($files as $url)
        {
            if($this->url_exists($url))
            {
                $fileName = 'document'.'-'.rand().'-'.time().'.'.pathinfo($url, PATHINFO_EXTENSION);
                $routeFile = $this->routeProducts.$product->id.'/documents/'.$fileName;

                $contents   = file_get_contents($url);
                Storage::put($this->routeFile.$routeFile, $contents);

                $product->files()->create([ 'name' => $fileName, 'type'=> 'documents', 'url' => $routeFile]);
            }
        }
    }

    public function addImages($images, $product)
    {
        $images     = array_unique($this->stringToArray($images));
        $allowed    = ['jpg','png','jpeg','gif'];

        foreach($images as $url)
        {
            if($this->url_exists($url) && in_array(pathinfo($url, PATHINFO_EXTENSION), $allowed))
            {
                $imageName = 'image'.'-'.rand().'-'.time().'.'.pathinfo($url, PATHINFO_EXTENSION);
                $routeFile = $this->routeProducts.$product->id.'/images/'.$imageName;

                $contents   = file_get_contents($url);
                Storage::put($this->routeFile.$routeFile, $contents);


               

                /*$img = Image::make($url);
                $img->save($imageName, 20);*/

                /*$img = new \Imagick();
                $img->readImage($url);
                $img->setImageCompression(imagick::COMPRESSION_JPEG);
                $img->setImageCompressionQuality(90);
                $img->stripImage();
                $img->writeImage($this->routeFile.$this->routeProducts.$product->id.'/images/');*/

                $product->files()->create([ 'name' => $imageName, 'type'=> 'images', 'url' => $routeFile]);
            }
        }
    }

    public function addMainImg($url, $product)
    {
        $allowed    = ['jpg','png','jpeg','gif'];

        if($this->url_exists($url) && in_array(pathinfo($url, PATHINFO_EXTENSION), $allowed))
        {
            $generator     = new Generator();
            $imageName     = $generator->generate($product->name);
            $imageName     = $imageName . '-' . uniqid().'.'.pathinfo($url, PATHINFO_EXTENSION);
    
            $routeProducts = $this->routeProducts.$product->id.'/'.$imageName;
    
            $contents   = file_get_contents($url);
            Storage::put($this->routeFile.$routeProducts , $contents);
    
            $product->image()->create(['url' => $routeProducts]);
        }
    }

    public function store(Request $request)
    {
        if(Schema::hasTable('temp_product_files'))
        {
            $product_img = DB::table('temp_product_files')
                ->where('status','false')
                ->take(100)
                ->get();

            foreach($product_img as $value)
            {
                $product    = Products::find($value->product_id);

                //category/categorias
                if(!empty($value->categories))
                    $this->addCategories($value->categories, $product);
                    
                //tags/Etiquetas
                if(!empty($value->tags))
                    $this->addTags($value->tags, $product);
                    
                //files/Archivos
                if(!empty($value->files))
                    $this->addFiles($value->files, $product);

                //images/Galeria de imagenes
                if(!empty($value->galery_img))
                    $this->addImages($value->galery_img, $product);

                //main_img/Imagen principal
                if(!empty($value->main_img))
                    $this->addMainImg($value->main_img, $product);
                

                DB::table('temp_product_files')
                    ->where('id', $value->id)
                    ->update(['status' => 'true']);
            };

            //borra las filas que ya se ha gestionado
            DB::table('temp_product_files')
                ->where('status', 'true')
                ->delete();

            //si la tabla esta vacia la elimina
            if(empty(DB::table('temp_product_files')->count())){
                DB::unprepared( DB::raw( "DROP TABLE temp_product_files" ) );
            }

        }; 

    }

}
