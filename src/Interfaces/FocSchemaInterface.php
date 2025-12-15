<?php

namespace FOC\Interfaces;

/**
 * FocSchemaInterface
 *
 * Defines the contract for database schema definitions.
 *
 * Implementing classes must provide the database table name
 * and a list of column definitions used for schema creation
 * or validation purposes.
 */
interface FocSchemaInterface
{
    /**
     * Get the database table name.
     */
    public static function tableName(): string;

    /**
     * Get the table column definitions.
     */
    public static function columns(): array;
}
