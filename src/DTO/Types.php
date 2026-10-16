<?php

namespace SquirrelForge\Laravel\CoreSupport\DTO;

use Illuminate\Support\Traits\Macroable;
use Throwable;

class Types implements TypesContract
{
    use Macroable;

    /**
     * Cast to string value.
     * @param mixed $value
     * @param bool $nullable
     * @return string|null
     */
    public static function castString(mixed $value, bool $nullable = true): ?string
    {
        if ($nullable && empty($value)) return null;
        return (string)$value;
    }

    /**
     * Cast to integer value.
     * @param mixed $value
     * @param bool $nullable
     * @return int|null
     */
    public static function castInt(mixed $value, bool $nullable = true): ?int
    {
        if ($nullable && empty($value)) return null;
        return intval($value);
    }

    /**
     * Cast to boolean value.
     * @param mixed $value
     * @param bool $nullable
     * @return bool|null
     */
    public static function castBool(mixed $value, bool $nullable = true): ?bool
    {
        if ($nullable && empty($value) && $value !== false) return null;
        return !empty($value);
    }

    /**
     * Cast to array of strings.
     * @param mixed $value
     * @param bool $nullable
     * @return array|null
     */
    public static function castStringArray(mixed $value, bool $nullable = true): ?array
    {
        if ($nullable && empty($value)) return null;
        if (is_array($value)) {
            $cast = [];
            foreach ($value as $key => $item) {
                $item = static::castString($item, $nullable);
                if ($nullable || !is_null($item)) {
                    $cast[$key] = $item;
                }
            }
            return $cast;
        }
        return preg_split('/[\s,;]+/', (string)$value, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Cast value to type.
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    public static function cast(mixed $value, string $type = '?string'): mixed
    {
        $nullable = str_starts_with($type, '?');
        if ($nullable) $type = substr($type, 1);
        try {
            return call_user_func_array([static::class, 'cast' . ucfirst($type)], [
                'value' => $value,
                'nullable' => $nullable,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
        if ($nullable && empty($value)) return null;
        return $value;
    }
}
