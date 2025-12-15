<?php

namespace FOC\Classes\Posts;

/**
 * FocPostType
 *
 * Abstract base class for custom post types within the plugin.
 *
 * Provides a reusable template for registering post-types in WordPress,
 * including common arguments like labels, archive support, editor features,
 * and an optional menu icon.
 *
 * Concrete post-types (e.g., brands, slots, games) should extend this class
 * and define their own slug, name, singular name, and optionally a menu icon.
 *
 * This approach ensures a consistent and maintainable way to handle
 * multiple custom post types in the plugin.
 */
abstract class FocPostType
{
    /**
     * Post type slug.
     *
     * Must be defined by the child class.
     */
    abstract protected static function slug(): string;

    /**
     * Register the post-type with WordPress.
     *
     * Builds labels and arguments automatically based on
     * the child class implementation and calls register_post_type().
     */
    public static function register(): void
    {
        $labels = [
            'name' => static::getName(),
            'singular_name' => static::getSingularName(),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor'],
            'menu_icon' => static::menuIcon(),
        ];

        register_post_type(static::slug(), $args);
    }

    /**
     * Get the plural name of the post-type.
     *
     * Must be implemented by the child class.
     */
    abstract protected static function getName(): string;

    /**
     * Get the singular name of the post-type.
     *
     * Must be implemented by the child class.
     */
    abstract protected static function getSingularName(): string;

    /**
     * Get the menu icon URL or Dashicon class.
     *
     * Can be overridden by the child class to provide a custom icon.
     */
    protected static function menuIcon(): ?string
    {
        return null;
    }
}
