<?php

declare(strict_types = 1);

namespace Folded\Traits;

/**
 * Centralize methods to encode/decode a JSON string.
 *
 * @since 0.1.0
 */
trait CanManipulateJson
{
    /**
     * Returns a JSON string from an array.
     *
     * @param array<mixed> $data An array representing the JSON data.
     *
     * @since 0.1.0
     *
     * @example
     * $jsonString = self::arrayToJson([]);
     */
    public static function arrayToJsonString(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * Returns an array from a JSON string.
     *
     * @param string $jsonString The JSON string.
     *
     * @return array<mixed>
     *
     * @since 0.1.0
     *
     * @example
     * self::jsonStringToArray("[]");
     */
    public static function jsonStringToArray(string $jsonString): array
    {
        return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
    }
}
