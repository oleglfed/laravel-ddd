<?php
namespace oleglfed\LaravelDDD;

use Illuminate\Support\ServiceProvider;
use oleglfed\LaravelDDD\Commands\GenerateDomain;

class LaravelDddServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the API doc commands.
     *
     * @return void
     */
    public function register()
    {
        $this->app['make.domain'] = $this->app->share(function () {
            return new GenerateDomain();
        });

        $this->commands([
            'make.domain',
        ]);
    }
}