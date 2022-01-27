<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Models\Products;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;
use TaylorNetwork\UsernameGenerator\Generator;

class TaskDownloadImgProduct extends Command
{
    public $routeFile       = 'public/';
    public $routeProducts   = 'images/products/';

    public $image_format    = ['jpg', 'png', 'jpeg'];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:download_img_product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Descarga y registra todas las imagenes, de los productos cargados con el archivo cvs.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (Schema::hasTable('temp_product_files')) {
            $product_img = DB::table('temp_product_files')
                ->where('status', 'false')
                ->take(80)
                ->get();

            foreach ($product_img as $value) {

                DB::table('temp_product_files')
                    ->where('id', $value->id)
                    ->update(['status' => 'true']);

                $product    = Products::find($value->product_id);

                if (!$product) {
                    Storage::append("archivo.txt","el producto no existe");
                    DB::table('temp_product_files')
                        ->where('id', $value->id)
                        ->delete();

                    continue;
                }

                //files/Archivos
                if (!empty($value->files))
                    $this->addFiles($value->files, $product);

                //images/Galeria de imagenes
                if (!empty($value->galery_img))
                    $this->addImages($value->galery_img, $product);

                //main_img/Imagen principal
                if (!empty($value->main_img))
                    $this->addMainImg($value->main_img, $product);
            }
        }
    }

    public function stringToArray($string)
    {
        $array = explode("#", $string);
        return $array;
    }

    public function addFiles($files, $product)
    {
        $files = array_unique($this->stringToArray($files));

        foreach ($files as $url) {
            $file_format    = strtolower(pathinfo($url, PATHINFO_EXTENSION));

            $response = Http::get($url);

            if ($response->successful()) {
                $fileName      = 'document' . '-' . rand() . '-' . time() . '.' . $file_format;
                $routeFile     = $this->routeProducts . $product->id . '/documents/' . $fileName;

                DB::beginTransaction();

                try {
                    $contents   = file_get_contents($url);
                    Storage::disk('local')->put($this->routeFile . $routeFile, $contents);
                    $product->files()->create(['name' => $fileName, 'type' => 'documents', 'url' => $routeFile]);
                } catch (\Exception $e) {
                    DB::rollBack();
                }
                DB::commit();
            }
        }
    }

    public function addImages($images, $product)
    {
        $images = array_unique($this->stringToArray($images));

        foreach ($images as $url) {
            $file_format = strtolower(pathinfo($url, PATHINFO_EXTENSION));

            $response = Http::get($url);

            if ($response->successful() && in_array($file_format, $this->image_format)) {
                $imageName = 'image' . '-' . rand() . '-' . time() . '.' . $file_format;
                $routeFile = $this->routeProducts . $product->id . '/images/' . $imageName;

                DB::beginTransaction();

                try {
                    $contents   = file_get_contents($url);
                    Storage::disk('local')->put($this->routeFile . $routeFile, $contents);
                    $product->files()->create(['name' => $imageName, 'type' => 'images', 'url' => $routeFile]);
                } catch (\Exception $e) {
                    DB::rollBack();
                }

                DB::commit();
            }
        }
    }

    public function addMainImg($url, $product)
    {
        Storage::append("archivo.txt","entro a imagen principal");
        
        $file_format = strtolower(pathinfo($url, PATHINFO_EXTENSION));

        $response = Http::get($url);
        
        if ($response->successful() && in_array($file_format, $this->image_format)) {

            Storage::append("archivo.txt","existe la url");

            $generator     = new Generator();
            $imageName     = $generator->generate($product->name);
            $imageName     = $imageName . '-' . uniqid() . '.' . $file_format;

            $routeProducts = $this->routeProducts . $product->id . '/' . $imageName;

            Storage::append("archivo.txt",$this->routeFile . $routeProducts);

            DB::beginTransaction();

            try {
                $contents   = file_get_contents($url);
                Storage::disk('local')->put($this->routeFile . $routeProducts, $contents);
                $product->image()->create(['url' => $routeProducts]);

                Storage::append("archivo.txt","guardo con exito");

            } catch (\Exception $e) {
                Storage::append("archivo.txt",$e);
                DB::rollBack();
            }
            DB::commit();
        }else{
            Storage::append("archivo.txt","no existe la url");
        }
    }
}
