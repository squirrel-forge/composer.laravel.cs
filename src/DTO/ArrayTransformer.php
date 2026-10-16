<?php

namespace SquirrelForge\Laravel\CoreSupport\DTO;

use Closure;

/**
 * @phpstan-type DTOFieldDefinition object{to: string, type?: string, value?: Closure(mixed):mixed, split?: bool, keep?: bool}
 * @phpstan-type DTOMapDefinition array<string, DTOFieldDefinition>
 */

class ArrayTransformer
{
    /** @var string $typesClass Static class interface SquirrelForge\Laravel\CoreSupport\DTO\TypesContract */
    public static string $typesClass = Types::class;

    /** @var array<string, DTOMapDefinition> $maps All defined map types. */
    protected static array $maps = [];

    /**
     * Define map by name.
     * @param string $name
     * @param DTOMapDefinition $map
     * @return void
     */
    public static function define(string $name, array $map): void
    {
        static::$maps[$name] = $map;
    }

    /**
     * Get map definition.
     * @param string $name
     * @return DTOMapDefinition|null
     */
    public static function get(string $name): ?array
    {
        return static::$maps[$name] ?? null;
    }

    /**
     * Get map field names.
     * @param string $name
     * @return string[]|null
     */
    public static function fieldNames(string $name): ?array
    {
        $map = static::get($name);
        if (empty($map)) return null;
        return array_keys($map);
    }

    /**
     * Get field by name.
     * @param string $name
     * @param string $field
     * @return DTOFieldDefinition|null
     */
    public static function getField(string $name, string $field): ?object
    {
        return static::$maps[$name][$field] ?? null;
    }

    /**
     * Get field target name.
     * @param string $name
     * @param string $from
     * @return string
     */
    public static function toField(string $name, string $from): string
    {
        return static::getField($name, $from)?->to ?? $from;
    }

    /**
     * Get processed field value.
     * @param string $name
     * @param string $from
     * @param mixed $value
     * @return mixed
     */
    public static function toValue(string $name, string $from, mixed $value): mixed
    {
        // Get field data
        $field = static::getField($name, $from);

        // Convert value to defined type
        $type = $field->type ?? null;
        if (isset($type)) {
            $value = static::$typesClass::cast($value, $type);
        }

        // Custom process value
        $process = $field->value ?? null;
        if ($process instanceof Closure) {
            return call_user_func($process, $value);
        }
        return $value;
    }

    /**
     * Transform source with given name and fields.
     * @param string $name
     * @param array $source
     * @param string[] $fields
     * @param array|null $with
     * @param bool $notNull
     * @return array
     */
    public static function transform(
        string $name,
        array $source,
        array $fields,
        ?array $with = null,
        bool $notNull = false,
    ): array
    {
        $transformed = [];
        foreach ($fields as $field) {

            // Get target field name
            $to = static::toField($name, $field);

            // Get and set target field value
            $transformed[$to] = static::toValue($name, $field, $source[$field] ?? null);

            // Only if value is array
            if (is_array($transformed[$to])) {
                $fieldData = static::getField($name, $field);

                // Check field data for split
                if (($fieldData->split ?? null) === true && !empty($fieldData->value)) {
                    $replaced = false;

                    // Append each split value to transformed values
                    $cycle = $transformed[$to];
                    foreach ($cycle as $splitField => $splitValue) {
                        $transformed[$splitField] = $splitValue;
                        if ($splitField === $to) $replaced = true;
                    }

                    // Remove original value if not replaced and keep is not enabled
                    if (!$replaced && ($fieldData->keep ?? null) !== true) {
                        unset($transformed[$to]);
                    }
                }
            }
        }

        // Merge any additional values
        if ($with) $transformed = array_merge($transformed, $with);

        // Remove all null values
        if ($notNull) $transformed = array_filter($transformed, fn($value) => !is_null($value));
        return $transformed;
    }
}
