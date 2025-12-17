<?php

namespace FOC\Models\Abstracts;

/**
 * FocAbstractModel
 *
 * Represents an entity synchronized from the external API.
 *
 * This model acts as a mapping layer between the API response
 * and the WordPress custom post-type.
 *
 * API fields may be returned in camelCase format, while WordPress
 * meta-keys follow snake_case naming. The model provides a key map
 * to normalize API fields before they are stored as post-meta.
 *
 * The list of fillable attributes defines which mapped meta-fields
 * are allowed to be created or updated during the import process.
 *
 * Actual persistence (post-creation, meta updates) is handled
 * by higher-level services or import jobs.
 */
abstract class FocAbstractModel
{
    /**
     * Maps API response keys (camelCase) to WordPress meta-keys (snake_case).
     *
     * This mapping is applied when normalizing data received
     * from the API before saving it to the custom post-type.
     */
    public static function keyMap(): array
    {
        return [];
    }

    /**
     * Returns the list of allowed WordPress meta-fields
     * for the custom post-type.
     *
     * Must be defined in the child model.
     */
    abstract public static function getFillable(): array;
}
