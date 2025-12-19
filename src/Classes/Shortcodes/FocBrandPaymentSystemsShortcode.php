<?php

namespace FOC\Classes\Shortcodes;

use FOC\Classes\Shortcodes\Abstracts\FocAbstractShortcode;

/**
 * Brand payment systems shortcode.
 *
 * Renders a list of available payment systems for the current brand.
 * This shortcode works only on single Brand pages and relies on
 * the current post-context to retrieve data from post-meta.
 *
 * Shortcode usage:
 *   [foc_brand_payment_systems]
 *
 * The output is rendered via a template located at:
 *   /templates/shortcodes/brand-payment-systems.php
 */
class FocBrandPaymentSystemsShortcode extends FocAbstractShortcode
{
    /**
     * Shortcode tag
     */
    protected static function tag(): string
    {
        return 'foc_brand_payment_systems';
    }

    /**
     * Template path relative to /templates
     */
    protected static function template(): string
    {
        return 'shortcodes/brand-payment-systems.php';
    }
}
