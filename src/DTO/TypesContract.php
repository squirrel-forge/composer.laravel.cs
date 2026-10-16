<?php

namespace SquirrelForge\Laravel\CoreSupport\DTO;

interface TypesContract
{
    /**
     * Cast value to type.
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    public static function cast(mixed $value, string $type = '?string'): mixed;
}
