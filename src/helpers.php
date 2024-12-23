<?php

namespace SquirrelForge\Laravel\CoreSupport;

use Illuminate\Http\Request;

if (!function_exists(__NAMESPACE__ . '\\getClientIp')) {

    /**
     * Get actual client ip
     * @param null|Request $request
     * @return mixed
     */
    function clientIp(Request $request = null): mixed
    {
        // Get ip from env values
        $names = config('sqf-fs.debug.env');
        if (isset($names) && is_array($names)) {
            foreach ($names as $name) {
                if (isset($_SERVER[$name])) return $_SERVER[$name];
            }
        }

        // Get request if missing
        if (!isset($request)) $request = request();

        // Return request ip
        return $request->ip();
    }
}

if (!function_exists(__NAMESPACE__ . '\\findDirInParentStructure')) {

    /**
     * Locate directory or link inside parent structure of path
     * @param string $find
     * @param string $path
     * @param boolean $resolve
     * @param integer $limit
     * @return null|string
     */
    function findDirInParentStructure(string $find, string $path, bool $resolve = true, int $limit = 4): ?string
    {
        $x = 0;
        $b = basename($path);
        while ($b != $find) {
            $path = dirname($path);
            $check = $path . DIRECTORY_SEPARATOR . $find;
            if (is_dir($check) || ($resolve && is_link($check))) {
                if (is_link($check)) return readlink($check);
                return $check;
            }
            $x++;
            if ($x > $limit) return null;
        }
        return $path;
    }
}

if (!function_exists(__NAMESPACE__ . '\\requireStorageFolderStructure')) {

    /**
     * Ensure storage dir has the appropriate structure
     * @param string $path
     * @param int $mask
     * @return void
     */
    function requireStorageFolderStructure(string $path, int $mask = 022): void
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $dirs = [
            ['app'],
            ['app', 'public'],
            ['framework'],
            ['framework', 'cache'],
            ['framework', 'sessions'],
            ['framework', 'testing'],
            ['framework', 'views'],
            ['logs'],
        ];
        umask($mask);
        foreach ($dirs as $dir) {
            $required = $path . implode(DIRECTORY_SEPARATOR, $dir);
            if (!is_dir($required)) mkdir($required);
        }
    }
}

if (!function_exists(__NAMESPACE__ . '\\joinAndResolvePaths')) {
    function joinAndResolvePaths(string ...$paths): string
    {
        $joined = implode(DIRECTORY_SEPARATOR, $paths);

        // Maintain first and last separator state
        $root = $joined[0] === DIRECTORY_SEPARATOR;
        $trailing = $joined[mb_strlen($joined) - 1] === DIRECTORY_SEPARATOR;

        $resolved = [];
        $segments = explode(DIRECTORY_SEPARATOR, $joined);
        foreach ($segments as $segment) {
            $append = true;

            // Empty or current path segments can be ignored
            if (empty($segment) || $segment === '.') continue;

            // Parent path segments need to be resolved
            if ($segment === '..') {

                // It's the first segment
                if (empty($resolved)) {

                    // We want to keep this segment, but the path cannot be a root path.
                    if ($root) $root = false;

                    // It's not the first and our previous segment is not a parent path segment.
                } else if ($resolved[count($resolved) - 1] !== '..') {

                    // Remove the previous segment.
                    array_pop($resolved);

                    // And here we do not wish to add the parent path segment since we resolved it.
                    $append = false;
                }
            }

            // Only append segment if required
            if ($append) $resolved[] = $segment;
        }
        return ($root ? DIRECTORY_SEPARATOR : '') .
            implode(DIRECTORY_SEPARATOR, $resolved) .
            ($trailing ? DIRECTORY_SEPARATOR : '');
    }
}
