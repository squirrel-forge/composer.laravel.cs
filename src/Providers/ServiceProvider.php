<?php

namespace SquirrelForge\Laravel\CoreSupport\Providers;

use Illuminate\Support\ServiceProvider as Provider;
use SquirrelForge\Laravel\CoreSupport\Console\Commands\MovePublicDirectoryCommand;
use SquirrelForge\Laravel\CoreSupport\Http\Middleware\DynamicDebug;
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
        //
    }

    /**
     * Bootstrap services.
     * @return void
     */
    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([MovePublicDirectoryCommand::class]);
        }

        // Register middleware
        $this->app->get('router')->aliasMiddleware('sqf-cs-dd', DynamicDebug::class);
        $this->app->get('router')->aliasMiddleware('sqf-cs-gh', ResponseHeaders::class);
        if (config('sqf-cs.debug.enabled')) {
            $this->app->get('router')->pushMiddlewareToGroup('web', DynamicDebug::class);
        }
        if (!empty(config('sqf-cs.headers'))) {
            $this->app->get('router')->pushMiddlewareToGroup('web', ResponseHeaders::class);
        }

        // Publish config
        $base_dir = dirname(__DIR__, 2);
        $config_src = implode(DIRECTORY_SEPARATOR, [$base_dir, 'resources', 'config', '']);
        $this->publishes([$config_src . 'config.php' => config_path('sqf-cs.php')], ['sqf-cs', 'config']);
    }
}
