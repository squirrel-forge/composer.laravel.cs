# composer.laravel.cs
## Squirrel-Forge Laravel Core Support

Composer module: **squirrel-forge/lara-cs**

Composer repository entry:
```json
{
    "type": "vcs",
    "url": "git@github.com:squirrel-forge/composer.laravel.cs.git",
    "no-api": true
}
```

## Middleware configuration

Check the [configuration](resources/config/config.php) for detailed option descriptions.

### Dynamic debug

[Middleware](src/Http/Middleware/DynamicDebug.php) that allows for runtime debug mode activation.

### Response headers

[Middleware](src/Http/Middleware/ResponseHeaders.php) that sets security headers for every request.

## Moving the public directory

To copy or symlink your public directory to a new location, run the *mvpub* command.
Note that running this command from the appropriate user/context will prevent permission issues.

The *target* can be relative to laravel root or system absolute.
The *--cp* option defines, if and which files are copied instead of linked.

```shell
php artisan sqfcs:mvpub {target} {--cp=null|all|true|filename,dirname...}
```

Any *.php files that are copied and not linked, will have "../" replaced with the new relative path to the laravel root.

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
