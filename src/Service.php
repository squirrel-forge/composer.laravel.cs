<?php

namespace SquirrelForge\Laravel\CoreSupport;

use Illuminate\Contracts\Foundation\Application;
use SquirrelForge\Laravel\CoreSupport\Exceptions\InvalidLocateCallException;
use SquirrelForge\Laravel\CoreSupport\Exceptions\MissingAppException;
use SquirrelForge\Laravel\CoreSupport\Exceptions\MissingBaseDirException;
use SquirrelForge\Laravel\CoreSupport\Exceptions\DirectoryNotFoundException;

/**
 * Service class
 */
class Service {

    /** @var null|Application $app Laravel application instance. */
    public static ?Application $app = null;

    /** @var int $iterationLimit Maximum parent levels that are checked. */
    public static int $iterationLimit = 4;

    /** @var boolean $resolveLinks Follow symlinks when locating a directory. */
    public static bool $resolveLinks = true;

    /** @var null|string $baseDir Override default base dir. */
    public static ?string $baseDir = null;

    /** @var int $umask Set umask for storage directory creating. */
    public static int $umask = 022;

    /** @var boolean $throwExceptions Set this value to false if you wish to die silently. */
    public static bool $throwExceptions = true;

    /** @var bool $hasRunEnv Env location can only be run once. */
    protected static bool $hasRunEnv = false;

    /** @var bool $hasRunStorage Storage location can only be run once. */
    protected static bool $hasRunStorage = false;

    /**
     * Locate env file dir
     * @param string $name
     * @param Application|null $app
     * @param string|null $base
     * @return void
     * @throws MissingAppException
     * @throws MissingBaseDirException
     * @throws DirectoryNotFoundException
     * @throws InvalidLocateCallException
     */
    public static function locateEnvDir(string $name, Application $app = null, string $base = null): void
    {
        if (static::$hasRunEnv) {
            throw new InvalidLocateCallException('Locate env can only be called once from the kernel constructor');
        }
        static::$hasRunEnv = true;
        static::defaults($app);

        // Check base path and app instance
        $base = static::validate($base);

        // Attempt to find the name located in one of the parents
        $found = findDirInParentStructure($name, $base, static::$resolveLinks, static::$iterationLimit);

        // Not found within limits
        if ($found === null) {
            if (!static::$throwExceptions) return;
            throw new DirectoryNotFoundException('Environment folder not found in: "' . $base . '", or any of its parents.');
        }

        // Set config source outside of root
        static::$app->useEnvironmentPath($found);
    }

    /**
     * Locate storage dir
     * @param string $name
     * @param Application|null $app
     * @param string|null $base
     * @return void
     * @throws MissingAppException
     * @throws MissingBaseDirException
     * @throws DirectoryNotFoundException
     * @throws InvalidLocateCallException
     */
    public static function locateStorageDir(string $name, Application $app = null, string $base = null): void
    {
        if (static::$hasRunStorage) {
            throw new InvalidLocateCallException('Locate storage can only be called once from the kernel constructor');
        }
        static::$hasRunStorage = true;
        static::defaults($app);

        // Check base path and app instance
        $base = static::validate($base);

        // Attempt to find the name located in one of the parents
        $found = findDirInParentStructure($name, $base, static::$resolveLinks, static::$iterationLimit);

        // Not found within limits
        if ($found === null) {
            if (!static::$throwExceptions) return;
            throw new DirectoryNotFoundException('Cache folder not found in: "' . $base . '", or any of its parents.');
        }

        // Ensure nested folder structure
        requireStorageFolderStructure($found, static::$umask);
        static::$app->useStoragePath($found);
    }

    /**
     * Validate service options
     * @param string|null $base
     * @return string
     * @throws MissingBaseDirException
     * @throws MissingAppException
     */
    protected static function validate(string $base = null): string
    {
        if (!isset(static::$app)) throw new MissingAppException('Service::$app must be set before use');
        if (empty($base)) {
            if (!isset(static::$baseDir)) {
                throw new MissingBaseDirException('Service::$baseDir must be set before use');
            }
            return static::$baseDir;
        }
        return $base;
    }

    /**
     * Set baseDir defaults
     * @param Application|null $app
     * @return void
     */
    protected static function defaults(Application $app = null): void
    {
        if ($app && !static::$app) static::$app = $app;
        if (!static::$baseDir) static::$baseDir = base_path();
    }
}
