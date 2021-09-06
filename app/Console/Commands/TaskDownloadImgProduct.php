<?php

namespace App\Console\Commands;

use App\Models\Products;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Storage;
use TaylorNetwork\UsernameGenerator\Generator;

class TaskDownloadImgProduct extends Command
{
    public $routeFile       = 'public/';
    public $routeProducts   = 'images/products/';
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
        if(Schema::hasTable('temp_product_img')){

            $product_img = DB::table('temp_product_img')
                ->where('status','false')
                ->take(450)
                ->get();

            foreach($product_img as $value)
            {
                $url           = $value->img;
                $product       = Products::find($value->product_id);
            
                $generator     = new Generator();
                $imageName     = $generator->generate($product->name);
                $imageName     = $imageName . '-' . uniqid().'.'.pathinfo($url, PATHINFO_EXTENSION);

                $routeProducts = $this->routeProducts.$product->id.'/'.$imageName;

                $contents   = file_get_contents($url);
                Storage::put($this->routeFile.$routeProducts , $contents);

                $product->image()->create(['url' => $routeProducts]);

                DB::table('temp_product_img')
                    ->where('id', $value->id)
                    ->update(['status' => 'true']);
            };

            //borra las filas que ya se ha gestionado
            DB::table('temp_product_img')
                ->where('status', 'true')
                ->delete();


            //si la tabla esta vacia la elimina
            if(empty(DB::table('temp_product_img')->count())){
                DB::unprepared( DB::raw( "DROP TABLE temp_product_img" ) );
            }

        };
    }
}
