<?php

namespace FOC\Database;

use FOC\Interfaces\FocSchemaInterface;
use FOC\Database\Schemas\FocBrandsSchema;

/**
 * FocMigrator
 *
 * Handles database schema migrations for the plugin.
 *
 * This class is responsible for creating and dropping all
 * plugin-related database tables based on registered
 * schema definitions.
 */
class FocMigrator
{
    /**
     * List of all plugin database schema classes.
     *
     * Each schema must implement {@see FocSchemaInterface}.
     */
    protected static array $schemas = [
        FocBrandsSchema::class,
    ];

    /**
     * Run database migrations.
     *
     * Creates or updates all plugin tables using WordPress dbDelta,
     * based on the registered schema definitions.
     */
    public static function migrate(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        foreach (self::$schemas as $schemaClass) {
            $schema = $schemaClass;

            $table = $wpdb->prefix . $schema::tableName();
            $columnsSql = implode(",\n", $schema::columns());

            $sql = "CREATE TABLE $table (
                $columnsSql
            ) $charset;";

            dbDelta($sql);
        }
    }

    /**
     * Drop all plugin database tables.
     *
     * Intended to be used during plugin uninstall
     * or full reset operations.
     */
    public static function dropAll(): void
    {
        global $wpdb;

        foreach (self::$schemas as $schemaClass) {
            $table = $wpdb->prefix . $schemaClass::tableName();
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}
