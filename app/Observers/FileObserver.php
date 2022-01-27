<?php

namespace App\Observers;

use File;
use App\Models\Files;
use Illuminate\Support\Facades\Storage;

class FileObserver
{
    public $routeFile = 'public/';
    /**
     * Handle the Files "created" event.
     *
     * @param  \App\Models\Files  $files
     * @return void
     */
    public function created(Files $files)
    {
        $file = Files::find($files->id);
        $file->size =  Storage::disk('local')->size($this->routeFile.$file->url);
        $file->save();

    }

    /**
     * Handle the Files "updated" event.
     *
     * @param  \App\Models\Files  $files
     * @return void
     */
    public function updated(Files $files)
    {
        $file = Files::find($files->id);
        $file->size =  Storage::disk('local')->size($this->routeFile.$file->url);
        $file->save();
    }

    /**
     * Handle the Files "deleted" event.
     *
     * @param  \App\Models\Files  $files
     * @return void
     */
    public function deleted(Files $files)
    {
        //
    }

    /**
     * Handle the Files "restored" event.
     *
     * @param  \App\Models\Files  $files
     * @return void
     */
    public function restored(Files $files)
    {
        //
    }

    /**
     * Handle the Files "force deleted" event.
     *
     * @param  \App\Models\Files  $files
     * @return void
     */
    public function forceDeleted(Files $files)
    {
        //
    }
}
