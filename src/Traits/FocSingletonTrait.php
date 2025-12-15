<?php

namespace FOC\Traits;

/**
 * Trait FocSingletonTrait
 *
 * Provides a simple implementation of the Singleton pattern.
 * Ensures that only one instance of a class using this trait can exist.
 */
trait FocSingletonTrait
{
    /**
     * Holds the single instance of the class.
     */
    private static ?self $instance = null;

    /**
     * Returns the single instance of the class.
     * If it does not exist yet, it will be created automatically.
     */
    public static function instance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Prevent cloning of the singleton instance.
     */
    public function __clone(): void
    {
    }

    /**
     * Prevent unserializing of the singleton instance.
     */
    public function __wakeup(): void
    {
    }
}