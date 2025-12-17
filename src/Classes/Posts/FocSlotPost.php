<?php

namespace FOC\Classes\Posts;

use FOC\Classes\Posts\Abstracts\FocAbstractPostType;
use FOC\Models\FocSlotModel;

/**
 * FocSlotPost
 *
 * Custom post-type class for Slots.
 *
 * Extends the abstract {@see FocAbstractPostType} class and defines
 * the specific slug, plural name, and singular name for the
 * "Brand" post-type.
 *
 * This class can be registered via {@see FocAbstractPostType::register()}
 * to make the Slots post-type available in WordPress.
 */
class FocSlotPost extends FocAbstractPostType
{
    /**
     * Post type slug.
     */
    protected static function slug(): string
    {
        return 'slot';
    }

    /**
     * Get the plural name of the post-type.
     */
    protected static function getName(): string
    {
        return 'Slots';
    }

    /**
     * Get the singular name of the post-type.
     */
    protected static function getSingularName(): string
    {
        return 'Slot';
    }

    /**
     * Get the menu icon URL or Dashicon class.
     */
    protected static function menuIcon(): ?string
    {
        return 'dashicons-games';
    }

    /**
     * Model class that provides meta-fields
     */
    protected static function model(): string
    {
        return FocSlotModel::class;
    }
}