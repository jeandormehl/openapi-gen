<?php

namespace Rapid\OAS\Providers;

use Rapid\OAS\OpenApi;
use Illuminate\Support\Str;
use Rapid\OAS\Console\Commands\YamlGenerator;
use Rapid\OAS\Http\Controllers\DocsController;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton(OpenApi::class);

        if ($this->app->runningInConsole()) {
            $this->commands([YamlGenerator::class]);
        }

        if (!$this->isLumen()) {
            $this->mergeConfigFrom($this->configPath(), 'oas');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->registerRoute();

        if (!$this->isLumen()) {
            $this->publishes([$this->configPath() => config_path('oas.php')]);
        }
    }

    /**
     * Register OpenApi route.
     */
    protected function registerRoute()
    {
        $router = $this->getRouter();

        $router->group([
            'middleware' => config('oas.route.middleware', []),
        ], function () use ($router) {
            $router->get($this->getPath(), '\\'.DocsController::class.'@docs');
        });
    }

    /**
     * Get the router.
     */
    protected function getRouter()
    {
        return app('router');
    }

    /**
     * Sanitize the OpenApi uri.
     *
     * @return string
     */
    protected function getPath()
    {
        return '/'
            .\ltrim(config('oas.route.prefix', ''), '/')
            .'/'
            .\ltrim(config('oas.route.path', 'docs'), '/');
    }

    /**
     * Determine if app is Lumen.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return Str::contains($this->app->version(), 'Lumen');
    }

    /**
     * Get the config path.
     *
     * @return string
     */
    protected function configPath()
    {
        return __DIR__.'/../../config/oas.php';
    }
}
