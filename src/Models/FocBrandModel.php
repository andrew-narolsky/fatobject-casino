<?php

namespace FOC\Models;

/**
 * FocBrandModel
 *
 * Represents a brand entity stored in the `api_brands` database table.
 * This model is responsible for mapping API response data to the database schema,
 * including automatic conversion of camelCase API fields to snake_case columns.
 *
 * It defines the list of fillable attributes allowed for insert and update
 * operations and relies on the base model for common database functionality.
 */
class FocBrandModel extends FocBaseModel
{
    /**
     * Mapping of API keys (camelCase) to database column names (snake_case).
     * Used to automatically convert API response fields to match DB schema.
     */
    public static function keyMap(): array
    {
        return [
            'yearEstablished' => 'year_established',
        ];
    }

    /**
     * List of allowed columns for insert/update operations.
     */
    public static function getFillable(): array
    {
        return [
            'brand_id',
            'url',
            'image',
            'year_established',
            'platform',
        ];
    }
}
