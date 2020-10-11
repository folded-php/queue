<?php

declare(strict_types = 1);

namespace Folded\Traits;

use JsonException;

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
     * @throws JsonException if an error occurs while converting the array into JSON string.
     *
     * @since 0.1.0
     *
     * @example
     * $jsonString = self::arrayToJson([]);
     */
    public static function arrayToJsonString(array $data): string
    {
        $jsonString = json_encode($data, JSON_THROW_ON_ERROR);

        // Ignoring because it might be a bug on phpstan
        // see: https://github.com/phpstan/phpstan/issues/3934
        // @phpstan-ignore-next-line
        if ($jsonString === false) {
            throw new JsonException(json_last_error_msg());
        }

        return $jsonString;
    }

    /**
     * Returns an array from a JSON string.
     *
     * @param string $jsonString The JSON string.
     *
     * @throws JsonException If an error occurs while parsing the JSON string.
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
        $jsonArray = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);

        // Ignoring because it might be a bug on phpstan
        // see: https://github.com/phpstan/phpstan/issues/3934
        // @phpstan-ignore-next-line
        if ($jsonArray === false || $jsonArray === null) {
            throw new JsonException("cannot decode json string");
        }

        return $jsonArray;
    }
}
