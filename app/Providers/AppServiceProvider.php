<?php

namespace App\Providers;

use App\Models\Files;
use App\Models\Image;
use App\Observers\FileObserver;
use App\Observers\ImageObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        /*Schema::defaultStringLength(191);*/
        Paginator::useBootstrap();
        Files::observe(FileObserver::class);
        Image::observe(ImageObserver::class);

    }
}
