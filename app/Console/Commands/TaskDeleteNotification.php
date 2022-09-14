<?php


use App\Models\Notifications;
namespace App\Console\Commands;
use Illuminate\Support\Facades\Storage;

use App\Models\Notifications;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TaskDeleteNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:delete_notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $date = Carbon::now()->add(-30, 'day')->format('Y-m-d');
        //Storage::append("archivoPrueba.txt",$date);
        Notifications::whereDate('created_at', '<', $date)->delete();
    }


}