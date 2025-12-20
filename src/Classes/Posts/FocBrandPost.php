<?php

namespace FOC\Classes\Posts;

use FOC\Classes\Posts\Abstracts\FocAbstractPostType;
use FOC\Models\FocBrandModel;

/**
 * FocBrandPost
 *
 * Custom post-type class for Brands.
 *
 * Extends the abstract {@see FocAbstractPostType} class and defines
 * the specific slug, plural name, and singular name for the
 * "Brand" post-type.
 *
 * This class can be registered via {@see FocAbstractPostType::register()}
 * to make the Brands post-type available in WordPress.
 */
class FocBrandPost extends FocAbstractPostType
{
    /**
     * Post type slug.
     */
    protected static function slug(): string
    {
        return 'brand';
    }

    /**
     * Get the plural name of the post-type.
     */
    protected static function getName(): string
    {
        return 'Brands';
    }

    /**
     * Get the singular name of the post-type.
     */
    protected static function getSingularName(): string
    {
        return 'Brand';
    }

    /**
     * Get the menu icon URL or Dashicon class.
     */
    protected static function menuIcon(): ?string
    {
        return 'dashicons-sos';
    }

    /**
     * Model class that provides meta-fields
     */
    protected static function model(): string
    {
        return FocBrandModel::class;
    }

    /**
     * Register additional meta-boxes for brand post-type
     */
    protected static function registerAdditionalMetaBoxes(): void
    {
        add_meta_box(
            static::slug() . '_shortcodes',
            'Shortcodes',
            function() {
                ?>
                <div>
                    <p><strong>Brand bonuses:</strong></p>
                    <code>[foc_brand_bonuses]</code>
                    <p><strong>Brand payment systems:</strong></p>
                    <code>[foc_brand_payment_systems]</code>
                    <p><strong>Brand software providers:</strong></p>
                    <code>[foc_brand_software_providers]</code>
                    <p><strong>Brand games:</strong></p>
                    <code>[foc_brand_games]</code>
                </div>
                <?php
            },
            static::slug(),
            'side',
            'low'
        );
    }
}