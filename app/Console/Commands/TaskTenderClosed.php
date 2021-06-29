<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TaskTenderClosed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:tender_closed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierra todas las licitaciones';

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
        // $texto = "[".date("Y-m-d H:i:s")."]: Hola, mi nombre es cristian";
        // Storage::append("archivo.txt", $texto);
        
    }
}
