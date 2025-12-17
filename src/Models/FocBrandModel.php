<?php

namespace FOC\Models;

use FOC\Models\Abstracts\FocAbstractModel;

/**
 * FocBrandModel
 *
 * Represents a Brand entity synchronized from the external API.
 *
 * This model acts as a mapping layer between the API response
 * and the WordPress custom post type `brand`.
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
class FocBrandModel extends FocAbstractModel
{
    /**
     * Maps API response keys (camelCase) to WordPress meta-keys (snake_case).
     *
     * This mapping is applied when normalizing brand data received
     * from the API before saving it to the `brand` custom post-type.
     */
    public static function keyMap(): array
    {
        return [
            'yearEstablished' => 'year_established',
        ];
    }

    /**
     * Returns the list of allowed WordPress meta-fields
     * for the `brand` custom post type.
     *
     * Only these fields (after API key mapping) will be
     * persisted during brand synchronization and import.
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
