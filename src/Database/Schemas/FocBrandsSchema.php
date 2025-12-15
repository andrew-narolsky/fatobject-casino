<?php

namespace FOC\Database\Schemas;

use FOC\Interfaces\FocSchemaInterface;

/**
 * FocBrandsSchema
 *
 * Defines the database schema for the brands table.
 *
 * This schema describes the structure of the `api_brands` table,
 * including column definitions, primary keys, and indexes used
 * to store brand data synchronized from the external API.
 */
class FocBrandsSchema implements FocSchemaInterface
{
    /**
     * Get the database table name (without WordPress prefix).
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'api_brands';
    }

    /**
     * Get the table column definitions.
     *
     * The returned array is used to build a CREATE TABLE statement
     * via WordPress dbDelta().
     *
     * @return array
     */
    public static function columns(): array
    {
        return [
            'id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY',
            'name VARCHAR(255) NOT NULL',
            'url VARCHAR(255) DEFAULT NULL',
            'image VARCHAR(255) DEFAULT NULL',
            'year_established INT(4) DEFAULT NULL',
            'platform VARCHAR(255) DEFAULT NULL',
            'UNIQUE KEY (id)',
        ];
    }
}
