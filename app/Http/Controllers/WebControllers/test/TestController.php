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
    public $routeProducts   = 'images/products/';

    public $image_format    = ['jpg','png','jpeg'];

    public function index()
    {
        return view('test.index');
    }
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:download_img_product';

   
    public function store()
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

                if(!$product)
                {
                    DB::table('temp_product_files')
                        ->where('id', $value->id)
                        ->delete();

                    continue;
                }

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
            }
        }
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
                $fileName = 'document'.'-'.rand().'-'.time().'.'.strtolower(pathinfo($url, PATHINFO_EXTENSION));
                $routeFile = $this->routeProducts.$product->id.'/documents/'.$fileName;

                $contents   = file_get_contents($url);
                Storage::put($this->routeFile.$routeFile, $contents);

                $product->files()->create([ 'name' => $fileName, 'type'=> 'documents', 'url' => $routeFile]);
            }
        }
    }

    public function addImages($images, $product)
    {
        $images = array_unique($this->stringToArray($images));

        foreach($images as $url)
        {
            if($this->url_exists($url) && in_array( strtolower(pathinfo($url, PATHINFO_EXTENSION)), $this->image_format))
            {
                $imageName = 'image'.'-'.rand().'-'.time().'.'.strtolower(pathinfo($url, PATHINFO_EXTENSION));
                $routeFile = $this->routeProducts.$product->id.'/images/'.$imageName;

                $contents   = file_get_contents($url);
                Storage::put($this->routeFile.$routeFile, $contents);

                $product->files()->create([ 'name' => $imageName, 'type'=> 'images', 'url' => $routeFile]);
            }
        }
    }

    public function addMainImg($url, $product)
    {
        if($this->url_exists($url) && in_array( strtolower(pathinfo($url, PATHINFO_EXTENSION)), $this->image_format))
        {
            $generator     = new Generator();
            $imageName     = $generator->generate($product->name);
            $imageName     = $imageName . '-' . uniqid().'.'.strtolower(pathinfo($url, PATHINFO_EXTENSION));
    
            $routeProducts = $this->routeProducts.$product->id.'/'.$imageName;
    
            $contents   = file_get_contents($url);
            Storage::put($this->routeFile.$routeProducts , $contents);
    
            $product->image()->create(['url' => $routeProducts]);
        }
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

}