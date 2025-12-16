<?php

namespace FOC\Classes\Posts;

use FOC\Models\FocSoftwareProviderModel;

/**
 * FocSoftwareProviderPost
 *
 * Custom post-type class for Software Providers.
 *
 * Extends the abstract {@see FocPostType} class and defines
 * the specific slug, plural name, and singular name for the
 * "Software Provider" post-type.
 *
 * This class can be registered via {@see FocPostType::register()}
 * to make the Software Providers post-type available in WordPress.
 */
class FocSoftwareProviderPost extends FocPostType
{
    /**
     * Post type slug.
     */
    protected static function slug(): string
    {
        return 'software-provider';
    }

    /**
     * Get the plural name of the post-type.
     */
    protected static function getName(): string
    {
        return 'Software Providers';
    }

    /**
     * Get the singular name of the post-type.
     */
    protected static function getSingularName(): string
    {
        return 'Software Provider';
    }

    /**
     * Get the menu icon URL or Dashicon class.
     */
    protected static function menuIcon(): ?string
    {
        return 'dashicons-privacy';
    }

    /**
     * Model class that provides meta-fields
     */
    protected static function model(): string
    {
        return FocSoftwareProviderModel::class;
    }
}