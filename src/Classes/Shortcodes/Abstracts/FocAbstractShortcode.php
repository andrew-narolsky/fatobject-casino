<?php

namespace FOC\Classes\Shortcodes\Abstracts;

use FOC\Classes\Template\FocTemplateLoader;

/**
 * Base abstract class for plugin shortcodes.
 *
 * This class provides a unified, template-driven architecture for all
 * shortcodes used in the FatObject Casino plugin.
 *
 * Responsibilities:
 * - Registers the shortcode using a defined tag
 * - Handles output buffering
 * - Renders a template via FocTemplateLoader
 *
 * Child shortcode classes must define:
 * - `tag()` → the shortcode name (e.g. "foc_brand_list")
 * - `template()` → the template path relative to the /templates directory
 *
 * This approach:
 * - Keeps shortcode logic clean and minimal
 * - Separates rendering from business logic
 * - Allows themes to override shortcode templates
 * - Ensures consistent behavior across all shortcodes
 */
abstract class FocAbstractShortcode
{
    /**
     * Default number of items displayed per page.
     *
     * Used by shortcodes that support pagination or "load more" functionality.
     * Can be overridden or ignored by child shortcodes if needed.
     */
    protected const int PER_PAGE = 10;

    /**
     * Shortcode tag
     */
    abstract protected static function tag(): string;

    /**
     * Template path relative to /templates
     */
    abstract protected static function template(): string;

    /**
     * Prepare data passed to the template
     */
    protected static function context(array $attributes): array
    {
        return [];
    }

    /**
     * Register shortcode
     */
    final public static function register(): void
    {
        add_shortcode(static::tag(), [static::class, 'render']);
    }

    /**
     * Render shortcode output
     */
    final public static function render(array $attributes = []): string
    {
        $attributes = shortcode_atts(
            [
                'pages' => static::PER_PAGE,
                'ids'       => '',
                'orderby'   => '',
                'order'     => 'DESC',
                'meta_key'  => '',
            ],
            $attributes,
            static::tag()
        );

        if (!empty($attributes['ids'])) {
            $attributes['ids'] = array_values(
                array_filter(
                    array_map('intval', explode(',', $attributes['ids']))
                )
            );
        } else {
            $attributes['ids'] = [];
        }

        ob_start();

        FocTemplateLoader::render(
            static::template(),
            static::context($attributes)
        );

        return ob_get_clean();
    }
}