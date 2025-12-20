<?php

namespace FOC\Classes\Shortcodes;

use FOC\Classes\Shortcodes\Abstracts\FocAbstractShortcode;

/**
 * Brand list shortcode.
 *
 * Renders a list of available brands.
 *
 * Shortcode usage:
 *   [foc_brand_list]
 *
 * The output is rendered via a template located at:
 *   /templates/shortcodes/brand-list.php
 */
class FocBrandListShortcode extends FocAbstractShortcode
{
    /**
     * Default number of items displayed per page.
     *
     * Used by shortcodes that support pagination or "load more" functionality.
     */
    protected const int PER_PAGE = 10;

    /**
     * Post-type slug.
     */
    protected const string POST_TYPE = 'brand';

    /**
     * Shortcode tag
     */
    protected static function tag(): string
    {
        return 'foc_brand_list';
    }

    /**
     * Template path relative to /templates
     */
    protected static function template(): string
    {
        return 'shortcodes/brand-list.php';
    }

    /**
     * Prepare data passed to the template
     */
    protected static function context(array $attributes): array
    {
        return [
            'per_page' => (int)($attributes['per_page'] ?: self::PER_PAGE),
            'page' => 1,
            'ids' => $attributes['ids'],
            'orderby' => $attributes['orderby'] ?: 'ID',
            'order' => $attributes['order'] ?: 'DESC',
            'meta_key' => $attributes['meta_key'],
            'post_type' => self::POST_TYPE,
        ];
    }
}
