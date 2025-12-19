<?php

namespace FOC\Classes\Shortcodes;

use FOC\Classes\Shortcodes\Abstracts\FocAbstractShortcode;

/**
 * Brand bonuses shortcode.
 *
 * Renders a list of bonuses for the current brand.
 * This shortcode works only on single Brand pages
 * and relies on the current post-context to retrieve
 * bonus data from post-meta.
 *
 * Shortcode usage:
 *   [foc_brand_bonuses]
 *
 * The output is rendered via a template located at:
 *   /templates/shortcodes/brand-bonuses.php
 */
class FocBrandBonusesShortcode extends FocAbstractShortcode
{
    /**
     * Shortcode tag
     */
    protected static function tag(): string
    {
        return 'foc_brand_bonuses';
    }

    /**
     * Template path relative to /templates
     */
    protected static function template(): string
    {
        return 'shortcodes/brand-bonuses.php';
    }
}
