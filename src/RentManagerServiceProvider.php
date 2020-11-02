<?php

namespace zinapse\RentManagerAPI;

use Illuminate\Support\ServiceProvider;

class RentManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();

        // $this->mergeConfigFrom(
        //     __DIR__ . '/config/rentmanager.php',
        //     'rentmanager'
        // );
    }

    public function boot()
    {
        // Add the config file to the project's config_path ( vendor:publish --tag=rentmanager )
        $this->publishes([
            __DIR__ . '/config/rentmanager.php' => config_path('rentmanager.php')
        ], 'rentmanager');
    }
}