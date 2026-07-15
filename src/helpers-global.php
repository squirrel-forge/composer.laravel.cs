<?php

use function SquirrelForge\Laravel\CoreSupport\sqfAsset as assetActual;

if (!function_exists('sqfAsset')) {

    /**
     * Resolve laravel asset
     * @param string $path
     * @param bool $pathOnly
     * @param bool $cache
     * @param bool|null $secure
     * @return string
     */
    function sqfAsset(string $path, bool $pathOnly = true, bool $cache = true, ?bool $secure = true): string
    {
        return assetActual($path, $pathOnly, $cache, $secure);
    }
}
