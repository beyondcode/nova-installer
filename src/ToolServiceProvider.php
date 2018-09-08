<?php

namespace Beyondcode\NovaInstaller;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Beyondcode\NovaInstaller\Http\Middleware\Authorize;
use Beyondcode\NovaInstaller\Console\Commands\NovaPackageDiscoveryCommand;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-installer');

        $this->app->booted(function () {
            $this->routes();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                NovaPackageDiscoveryCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('nova-installer.php'),
            ], 'config');
        }
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->namespace('Beyondcode\NovaInstaller\Http\Controllers')
                ->prefix('nova-vendor/beyondcode/nova-installer')
                ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \Beyondcode\NovaInstaller\Utils\Manipulation\Manipulator::class,
            \Beyondcode\NovaInstaller\Utils\Manipulation\Ast\AstStyleManipulator::class
        );

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'nova-installer');
    }
}
