<?php

namespace App\Observers;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageObserver
{
    public $routeFile = 'public/';
    /**
     * Handle the Image "created" event.
     *
     * @param  \App\Models\Image  $image
     * @return void
     */
    public function created(Image $image)
    {
        Image::where('url', $image->url)
            ->where('imageable_id', $image->imageable_id)
            ->where('imageable_type', $image->imageable_type)
            ->where('created_at', $image->created_at)
            ->where('updated_at', $image->updated_at)
            ->update(['size' => Storage::disk('local')
            ->size($this->routeFile.$image->url)]);
    }

    /**
     * Handle the Image "updated" event.
     *
     * @param  \App\Models\Image  $image
     * @return void
     */
    public function updated(Image $image)
    {
        Image::where('url', $image->url)
            ->where('imageable_id', $image->imageable_id)
            ->where('imageable_type', $image->imageable_type)
            ->where('created_at', $image->created_at)
            ->where('updated_at', $image->updated_at)
            ->update(['size' => Storage::disk('local')
            ->size($this->routeFile.$image->url)]);
    }

    /**
     * Handle the Image "deleted" event.
     *
     * @param  \App\Models\Image  $image
     * @return void
     */
    public function deleted(Image $image)
    {
        //
    }

    /**
     * Handle the Image "restored" event.
     *
     * @param  \App\Models\Image  $image
     * @return void
     */
    public function restored(Image $image)
    {
        //
    }

    /**
     * Handle the Image "force deleted" event.
     *
     * @param  \App\Models\Image  $image
     * @return void
     */
    public function forceDeleted(Image $image)
    {
        //
    }
}
