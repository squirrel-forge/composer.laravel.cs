# composer.laravel.cs
## Laravel squirrel-forge Laravel Core Support

Composer: squirrel-forge/lara-cs

## Middleware configuration

Check the [configuration](config/config.php) for detailed option descriptions.

### Dynamic debug

[Middleware](src/Http/Middleware/DynamicDebug.php) that allows for runtime debug mode activation.

### Response headers

[Middleware](src/Http/Middleware/ResponseHeaders.php) that sets security headers for every request.

## Directory locator

If you wish to use the directory locator services, you need
to implement the code in your kernel constructors as following
to allow for setting the directories before laravel uses them.

```php
use SquirrelForge\Laravel\CoreSupport\Service as SqfCs;
/**
 * Create a new HTTP kernel instance.
 *
 * @param  \Illuminate\Contracts\Foundation\Application  $app
 * @param  \Illuminate\Routing\Router  $router
 * @return void
 */
public function __construct(Application $app, Router $router)
{
    SqfCs::locateEnvDir('conf', $app);
    SqfCs::locateStorageDir('cache', $app);
    parent::__construct($app, $router);
}
```

```php
use SquirrelForge\Laravel\CoreSupport\Service as SqfCs;
/**
 * Create a new console kernel instance.
 *
 * @param  \Illuminate\Contracts\Foundation\Application  $app
 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
 * @return void
 */
public function __construct(Application $app, Dispatcher $events)
{
    SqfCs::locateEnvDir('conf', $app);
    SqfCs::locateStorageDir('cache', $app);
    parent::__construct($app, $events);
}
```
