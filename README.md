# composer.laravel.cs
## Squirrel-Forge Laravel Core Support

### Table of Contents

 - [Module information](#module-information)
 - [Installation](#installation)
   - [Publishing the config](#publishing-the-config)
   - [Example dotenv values](#example-dotenv-values)
 - [Middleware configuration](#middleware-configuration)
   - [Dynamic debug](#dynamic-debug)
   - [Response headers](#response-headers)
   - [Prevent request forgery](#prevent-request-forgery)
 - [Health route customization](#health-route-customization)
 - [Nested folder routing](#nested-folder-routing)
 - [Linking the public directory](#linking-the-public-directory)
   - [Moving the public directory](#moving-the-public-directory)
 - [Storage and environment directories](#storage-and-environment-directories)

## Module information

This is a core level module, that only supplies a few actual features and
mostly configuration and insight for routing and infrastructure requirements.

Composer module: **squirrel-forge/lara-cs**

Composer repository entry:
```json
{
    "type": "vcs",
    "url": "git@github.com:squirrel-forge/composer.laravel.cs.git",
    "no-api": true
}
```

## Installation

### Publishing the config

```shell
php artisan vendor:publish --tag=sqf-cs
```

### Example dotenv values

Example dotenv configuration, full list of values:

```dotenv
SQF_CS_USE=ip,range,keypass
SQF_CS_ENV=X-CLIENT-IP,REMOTE_ADDR
SQF_CS_IPS=000.000.000.000,0000:0000:0000:0000::0000:0000
SQF_CS_RANGES=000.000.000.00/00,0000:00:0000:0:0:0:0:0/00
SQF_CS_KEY=cxxa80quced65jg817xdxoalhhyn0blk
SQF_CS_PASS=g2czb0w289zl9xiom6kcy1nc0bbyfiao
SQF_CS_LIFETIME=Y-m-d-H
SQF_CS_LIMIT=60
SQF_CS_LOG=true
SQF_CS_CSP="default-src 'self'; script-src 'self' 'unsafe-inline'"
SQF_CS_CSRF=true
```

## Middleware configuration

Check the [configuration](resources/config/config.php) for detailed option descriptions.

### Dynamic debug

[Middleware](src/Http/Middleware/DynamicDebug.php) that allows for runtime debug mode activation.

### Response headers

[Middleware](src/Http/Middleware/ResponseHeaders.php) that sets global security headers for every response.

### Prevent request forgery

[Middleware](src/Http/Middleware/PreventRequestForgeryExtended.php) that provides extended options for the csrf token.

## Health route customization

Disable the default route by removing the "health" argument in your bootstrap:

*bootstrap/app.php*
```php
    ->withRouting(
        health: '/up',
    )
```

And then add the custom route handler to your application routes:

*routes/web.php*
```php
// Additional usages
use function SquirrelForge\Laravel\CoreSupport\getHealthRouteHandler;

// Bind the route
Route::get('/up', getHealthRouteHandler(
    template: 'sqf-cs::health-up',
    jsonUp: ['status' => 'up'],
    jsonDown: ['status' => 'down'],
));
```

You may also use the modules default template and publish it, to customize your html health view.

```shell
php artisan vendor:publish --tag=sqf-cs
```

## Nested folder routing

If your laravel runs in a domain root, but is requested via cdn url rewrite inside a nested path,
then follow these instructions to ensure all requests arrive where they are supposed to.

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

Or the recommended generic variant, which allows for dynamic prefixes, but with a downside.  
This does not allow the use of any defined slugs/paths in laravel routing as they will conflict:

```shell
# Rewrite routed asset paths
RewriteRule ^(.*)css/(.*)$ css/$2 [NC,QSA,L]
RewriteRule ^(.*)img/(.*)$ img/$2 [NC,QSA,L]
RewriteRule ^(.*)js/(.*)$ js/$2 [NC,QSA,L]
RewriteRule ^(.*)favicon.ico$ favicon.ico [NC,QSA,L]
RewriteRule ^(.*)robots.txt$ robots.txt [NC,QSA,L]
```

## Linking the public directory

To copy or symlink your public directory to a new location, run the *sqfcs:mvpub* command.  
Note that running this command from the appropriate user/context will prevent permission issues,
for example when running in a docker container you must run the command inside the container context.

The *target* can be relative to laravel root or system absolute.  
The *--cp* option defines, if and which files are copied instead of being linked.  
The option respects only file and folder names nested directly in the public dir,
though folders will be copied recursively.

```shell
php artisan sqfcs:mvpub {target} {--cp=all|filename,dirname,...}
```

Any *.php files that are copied and not linked,
will have "../" replaced with the new relative path to the laravel root.

### Moving the public directory

If you must **move/copy** the public directory and *not link* it to another location,
set following code in your *bootstrap/app.php* to let laravel know of the move:

#### Laravel 11+ bootstrap version

*bootstrap/app.php*
```php
// Additional usages
use function SquirrelForge\Laravel\CoreSupport\joinAndResolvePaths;

// Setup app
$app = Application::configure(basePath: dirname(__DIR__))
    // ...
    ->create();

// Define base path
$basePath = base_path();

// Move public path
$app->usePublicPath(joinAndResolvePaths($basePath, '../online/'));

// Return actual instance
return $app;

```

## Storage and environment directories

You may use the core helpers as following to set a custom storage
and/or environment path inside the *bootstrap/app.php*.

#### Laravel 11+ boostrap version

*bootstrap/app.php*
```php
// Additional usages
use function SquirrelForge\Laravel\CoreSupport\joinAndResolvePaths;
use function SquirrelForge\Laravel\CoreSupport\requireStorageFolderStructure;

// Setup app
$app = Application::configure(basePath: dirname(__DIR__))
    // ...
    ->create();

// Define base path
$basePath = base_path();

// Setup env directory
$app->useEnvironmentPath(joinAndResolvePaths($basePath, '../conf/'));

// Define storage path
$storagePath = joinAndResolvePaths($basePath, '../cache/');

// Require storage folder structure
requireStorageFolderStructure($storagePath);

// Setup storage directory
$app->useStoragePath($storagePath);

// Return actual instance
return $app;

```
