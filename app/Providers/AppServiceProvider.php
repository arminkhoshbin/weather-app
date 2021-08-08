<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
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
        $this->app->bind('GuzzleHttp\Client', function () {
            return new Client([
                'base_uri' => Config::get('api.weather_api.base_uri'),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
