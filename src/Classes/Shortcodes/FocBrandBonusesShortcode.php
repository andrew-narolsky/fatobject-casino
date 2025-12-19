<?php

namespace FOC\Classes\Shortcodes;

use FOC\Classes\Shortcodes\Abstracts\FocAbstractShortcode;

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
