<?php

namespace SquirrelForge\Laravel\CoreSupport\Providers;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider as Provider;
use SquirrelForge\Laravel\CoreSupport\Console\Commands\MovePublicDirectoryCommand;
use SquirrelForge\Laravel\CoreSupport\Http\Middleware\DynamicDebug;
use SquirrelForge\Laravel\CoreSupport\Http\Middleware\PreventRequestForgeryExtended;
use SquirrelForge\Laravel\CoreSupport\Http\Middleware\ResponseHeaders;

/**
 * Service provider.
 */
class ServiceProvider extends Provider
{
    /**
     * Register services.
     * @return void
     */
    public function register(): void
    {
        // Replace default request forgery middleware with extended version
        $this->app->bind(PreventRequestForgery::class, PreventRequestForgeryExtended::class);
    }

    /**
     * Bootstrap services.
     * @param Router $router
     * @return void
     */
    public function boot(Router $router): void
    {
        $base_dir = dirname(__DIR__, 2);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([MovePublicDirectoryCommand::class]);
        }

        // Register middleware
        $router->aliasMiddleware('sqf-cs-dd', DynamicDebug::class);
        $router->aliasMiddleware('sqf-cs-gh', ResponseHeaders::class);
        if (config('sqf-cs.debug.enabled')) {
            $router->pushMiddlewareToGroup('web', 'sqf-cs-dd');
        }
        if (!empty(config('sqf-cs.headers'))) {
            $router->pushMiddlewareToGroup('web', 'sqf-cs-gh');
        }

        // Load views
        $view_src = implode(DIRECTORY_SEPARATOR, [$base_dir, 'resources', 'views', '']);
        $this->loadViewsFrom($view_src, 'sqf-cs');

        // Publish views
        $this->publishes([$view_src => resource_path('views/vendor/sqf-cs')], 'views');

        // Publish config
        $config_src = implode(DIRECTORY_SEPARATOR, [$base_dir, 'resources', 'config', '']);
        $this->publishes([$config_src . 'config.php' => config_path('sqf-cs.php')], ['sqf-cs', 'config']);
    }
}
