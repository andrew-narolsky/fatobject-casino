<?php

namespace FOC\Models;

/**
 * Class FocBaseModel
 *
 * Base model class providing common database operations
 * for all plugin-related models. This class acts as a lightweight ORM
 * wrapper around WordPress `$wpdb`, including
 * - input sanitization
 * - CRUD operations (find, all, insert, update, delete)
 * - fillable field filtering
 * - table name definition via child classes
 *
 * Child models must define:
 * - protected string $table — the database table name
 * - protected array $fillable — an array of allowed columns for insert/update
 *
 * This design ensures consistency and reduces code duplication across models.
 */
abstract class FocBaseModel
{
    /**
     * Mapping of API keys (camelCase) to database column names (snake_case).
     *
     * Used to automatically convert API response fields to match DB schema.
     */
    public static function keyMap(): array {
        return [];
    }

    /**
     * List of allowed columns for insert/update operations.
     *
     * Must be defined in the child model.
     */
    abstract public static function getFillable(): array;
}
