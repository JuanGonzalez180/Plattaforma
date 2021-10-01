<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Models\Products;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

            if ($this->url_exists($url)) {
                $fileName      = 'document' . '-' . rand() . '-' . time() . '.' . $file_format;
                $routeFile     = $this->routeProducts . $product->id . '/documents/' . $fileName;

                try {
                    $contents   = file_get_contents($url);
                    $product->files()->create(['name' => $fileName, 'type' => 'documents', 'url' => $routeFile]);
                    Storage::disk('local')->put($this->routeFile . $routeFile, $contents);
                } catch (\Exception $e) {
                }
            }
        }
    }

    public function addImages($images, $product)
    {
        $images = array_unique($this->stringToArray($images));

        foreach ($images as $url) {
            $file_format = strtolower(pathinfo($url, PATHINFO_EXTENSION));

            if ($this->url_exists($url) && in_array($file_format, $this->image_format)) {
                $imageName = 'image' . '-' . rand() . '-' . time() . '.' . $file_format;
                $routeFile = $this->routeProducts . $product->id . '/images/' . $imageName;

                try {
                    $contents   = file_get_contents($url);
                    $product->files()->create(['name' => $imageName, 'type' => 'images', 'url' => $routeFile]);
                    Storage::disk('local')->put($this->routeFile . $routeFile, $contents);
                } catch (\Exception $e) {
                }
            }
        }
    }

    public function addMainImg($url, $product)
    {
        $file_format = strtolower(pathinfo($url, PATHINFO_EXTENSION));

        if ($this->url_exists($url) && in_array($file_format, $this->image_format)) {
            $generator     = new Generator();
            $imageName     = $generator->generate($product->name);
            $imageName     = $imageName . '-' . uniqid() . '.' . $file_format;

            $routeProducts = $this->routeProducts . $product->id . '/' . $imageName;


            try {
                $contents   = file_get_contents($url);
                $product->image()->create(['url' => $routeProducts]);
                Storage::disk('local')->put($this->routeFile . $routeProducts, $contents);
            } catch (\Exception $e) {
            }
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

    public function setImageCompressionQuality($imagePath, $quality)
    {
        $imagick = new \Imagick(realpath($imagePath));
        $imagick->setImageCompressionQuality($quality);
        header("Content-Type: image/jpg");
        echo $imagick->getImageBlob();
    }
}
