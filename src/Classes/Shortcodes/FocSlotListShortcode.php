<?php

namespace FOC\Classes\Shortcodes;

use FOC\Classes\Shortcodes\Abstracts\FocAbstractShortcode;

/**
 * Slot list shortcode.
 *
 * Renders a list of available slots.
 *
 * Shortcode usage:
 *   [foc_slot_list]
 *
 * The output is rendered via a template located at:
 *   /templates/shortcodes/slot-list.php
 */
class FocSlotListShortcode extends FocAbstractShortcode
{
    /**
     * Default number of items displayed per page.
     *
     * Used by shortcodes that support pagination or "load more" functionality.
     * Can be overridden or ignored by child shortcodes if needed.
     */
    protected const int PER_PAGE = 9;

    /**
     * Shortcode tag
     */
    protected static function tag(): string
    {
        return 'foc_slot_list';
    }

    /**
     * Template path relative to /templates
     */
    protected static function template(): string
    {
        return 'shortcodes/slot-list.php';
    }

    /**
     * Prepare data passed to the template
     */
    protected static function context(array $attributes): array
    {
        return [
            'pages' => (int) ($attributes['pages'] ?: static::PER_PAGE),
            'ids'      => $attributes['ids'],
            'orderby'  => $attributes['orderby'],
            'order'    => $attributes['order'],
            'meta_key' => $attributes['meta_key'],
        ];
    }
}
