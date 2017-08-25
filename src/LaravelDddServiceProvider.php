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
        $this->config = $this->app->make('config');
        $this->packages = $this->config->get('domains.user-binding');

        $files = \File::allFiles(config_path('domains'));

        foreach ($files as $file) {
            if ($bindings = $this->config->get('domains.'.basename($file->getFilename(), '.php'))) {
                if (array_has($bindings, 'providers')) {
                    $this->provideService(array_get($bindings, 'providers'));
                }

                if (array_has($bindings, 'repositories')) {
                    $this->provideRepositories(array_get($bindings, 'repositories'));
                }

                if (array_has($bindings, 'eloquent_repositories')) {
                    $this->provideEloquentRepositories(array_get($bindings, 'eloquent_repositories'));
                }
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDomain::class,
            ]);
        }
    }

    private function provideService(array $services)
    {
        foreach ($services as $key => $value) {
            $this->app->bind($key, $value);
        }
    }

    private function provideEloquentRepositories(array $eloquentRepositories)
    {
        foreach ($eloquentRepositories as $name => $eloquentRepository) {
            $this->app->bind($name, function ($app) use ($eloquentRepository, $name) {

                /*
                 * Laravel 5.4 doesn't support $app->make with parameters. Uses contextual binding instead
                 */
                $this->app->when($eloquentRepository['class'])
                    ->needs(\App\Infrastructures\EloquentAbstract::class)
                    ->give($eloquentRepository['model']);

                return $app->make($eloquentRepository['class']);
            });
        }
    }

    /**
     * Provide repositories from packages.
     *
     * @param array $repositories
     *
     * @return void
     */
    private function provideRepositories(array $repositories)
    {
        foreach ($repositories as $name => $provider) {
            $this->app->bind($name, $provider);
        }
    }
}
