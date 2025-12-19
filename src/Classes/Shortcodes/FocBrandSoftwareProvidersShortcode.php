<?php

namespace FOC\Classes\Shortcodes;

use FOC\Classes\Shortcodes\Abstracts\FocAbstractShortcode;

/**
 * Brand software providers shortcode.
 *
 * Renders a list of available software providers for the current brand.
 * This shortcode works only on single Brand pages and relies on
 * the current post-context to retrieve data from post-meta.
 *
 * Shortcode usage:
 *   [foc_brand_software_providers]
 *
 * The output is rendered via a template located at:
 *   /templates/shortcodes/brand-software-providers.php
 */
class FocBrandSoftwareProvidersShortcode extends FocAbstractShortcode
{
    /**
     * Shortcode tag
     */
    protected static function tag(): string
    {
        return 'foc_brand_software_providers';
    }

    /**
     * Template path relative to /templates
     */
    protected static function template(): string
    {
        return 'shortcodes/brand-software-providers.php';
    }
}
