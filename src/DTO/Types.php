<?php

namespace SquirrelForge\Laravel\CoreSupport\DTO;

use Illuminate\Support\Traits\Macroable;
use Throwable;

class Types implements TypesContract
{
    use Macroable;

    /** @var array|string[] $boolStringsTrue Strings that match a boolean true value. */
    public static array $boolStringsTrue = ['true', '1', 'on', 'yes', 'checked'];

    /** @var array|string[] $boolStringsFalse Strings that match a boolean false value. */
    public static array $boolStringsFalse = ['false', '0', 'off', 'no'];

    /**
     * Cast to string value.
     * @param mixed $value
     * @param bool $nullable
     * @return string|null
     */
    public static function castString(mixed $value, bool $nullable = true): ?string
    {
        if ($nullable && empty($value)) return null;
        if (is_null($value)) return '';
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
        if ($nullable && empty($value) && $value !== '0' && $value !== 0) return null;
        return intval($value);
    }

    /**
     * Cast to float value.
     * @param mixed $value
     * @param bool $nullable
     * @return int|null
     */
    public static function castFloat(mixed $value, bool $nullable = true): ?int
    {
        if ($nullable && empty($value) && $value !== '0' && $value !== 0) return null;
        if (is_string($value)) $value = trim(str_replace(',', '.', $value));
        return floatval($value);
    }

    /**
     * Cast to boolean value.
     * @param mixed $value
     * @param bool $nullable
     * @return bool|null
     */
    public static function castBool(mixed $value, bool $nullable = true): ?bool
    {
        if (is_string($value)) {
            if (in_array(mb_strtolower($value), static::$boolStringsTrue)) return true;
            if (in_array(mb_strtolower($value), static::$boolStringsFalse)) return false;
        }
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
        if (is_string($value)) {
            return preg_split('/[\s,;]+/', (string)$value, -1, PREG_SPLIT_NO_EMPTY);
        }
        return $nullable ? null : [];
    }

    /**
     * Cast to JSON string.
     * @param mixed $value
     * @param bool $nullable
     * @return string|null
     */
    public static function castJson(mixed $value, bool $nullable = true): ?string
    {
        if ($nullable && empty($value)) return null;
        if (is_string($value)) $value = [$value];
        if (is_array($value) || is_object($value) && get_class($value) === 'stdClass') {
            return json_encode($value);
        }
        return $nullable ? null : '{}';
    }

    /**
     * Auto cast value.
     * @param mixed $value
     * @param bool $nullable
     * @return mixed
     */
    public static function castAuto(mixed $value, bool $nullable = true): mixed
    {
        if (is_string($value)) {

            // Numeric checks
            if (is_numeric(trim(str_replace(',', '.', $value)))) {
                $value = trim(str_replace(',', '.', $value));
                if (mb_strpos($value, '.') !== false) {
                    $value = static::castFloat($value, $nullable);
                } else {
                    $value = static::castInt($value, $nullable);
                }

                // List/array structure
            } else if (preg_match('/[\s,;]+/', $value) === 1) {
                $value = static::castStringArray($value, $nullable);

                // Explicit boolean value
            } else if (in_array(mb_strtolower($value), static::$boolStringsTrue)
                || in_array(mb_strtolower($value), static::$boolStringsFalse)) {
                $value = static::castBool($value, $nullable);

                // TODO: detect date/time/timestamp?
            } else {
                $value = static::castString($value, $nullable);
            }
        } else if (is_object($value)) {
            if (get_class($value) === 'stdClass') {
                $value = static::castJson($value, $nullable);
            }

            // TODO: date + carbon object
        }
        if ($nullable && empty($value) && $value !== false) return null;
        return $value;
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
