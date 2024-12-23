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

## Nested folder routing

If your laravel runs in a domain root, but is requested via cdn url rewrite inside a nested path,
Then follow these instructions to ensure all requests arrive where they are supposed to.

Prefix your routes with the nested path:

```php
Route::group(['prefix' => '/prefixed/path/'], function () {
    // ... prefixed routes
});
```

Set your asset path/url in your *.env* file:

```dotenv
ASSET_URL=/prefixed/path/
```

To allow all your public assets to be accessible even when requested from a virtual subdirectory,
add following rules to your htaccess after the authorization header and before the trailing slash redirect.

```shell
# Rewrite routed asset paths
RewriteRule ^/prefixed/path/css/(.*)$ css/$2 [NC,QSA,L]
RewriteRule ^/prefixed/path/img/(.*)$ img/$2 [NC,QSA,L]
RewriteRule ^/prefixed/path/js/(.*)$ js/$2 [NC,QSA,L]
RewriteRule ^/prefixed/path/favicon.ico$ favicon.ico [NC,QSA,L]
RewriteRule ^/prefixed/path/robots.txt$ robots.txt [NC,QSA,L]
```

Or the recommended generic variant, which allows dynamic prefixes:

```shell
# Rewrite routed asset paths
RewriteRule ^(.*)css/(.*)$ css/$2 [NC,QSA,L]
RewriteRule ^(.*)img/(.*)$ img/$2 [NC,QSA,L]
RewriteRule ^(.*)js/(.*)$ js/$2 [NC,QSA,L]
RewriteRule ^(.*)favicon.ico$ favicon.ico [NC,QSA,L]
RewriteRule ^(.*)robots.txt$ robots.txt [NC,QSA,L]
```

## Moving the public directory

To copy or symlink your public directory to a new location, run the *sqfcs:mvpub* command.
Note that running this command from the appropriate user/context will prevent permission issues.

The *target* can be relative to laravel root or system absolute.
The *--cp* option defines, if and which files are copied instead of being linked.
The option respects only file and folder names nested directly in the public dir,
though folders will be copied recursively.

```shell
php artisan sqfcs:mvpub {target} {--cp=all|filename,dirname,...}
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
    SqfCs::locateEnvDir('env', $app);
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
    SqfCs::locateEnvDir('env', $app);
    SqfCs::locateStorageDir('cache', $app);
    parent::__construct($app, $events);
}
```
