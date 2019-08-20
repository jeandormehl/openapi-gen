<?php

namespace Rapid\OAS\Providers;

use Rapid\OAS\Http\Controllers\DocsController;

class DingoServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected function registerRoute()
    {
        $this->getRouter()->version(env('API_VERSION', 'v1'), [
            'middleware' => config('oas.route.middleware', []),
        ], function ($router) {
            $router->get($this->getPath(), '\\' . DocsController::class . '@docs');
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouter()
    {
        return app('api.router');
    }
}
