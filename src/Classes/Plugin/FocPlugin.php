<?php

namespace FOC\Classes\Plugin;

use FOC\Background\FocBrandImportProcess;
use FOC\Background\FocBrandSyncProcess;
use FOC\Background\FocResetAllDataProcess;
use FOC\Background\FocSlotImportProcess;
use FOC\Background\FocSlotSyncProcess;
use FOC\Classes\Import\FocImport;
use FOC\Classes\Posts\FocBrandPost;
use FOC\Classes\Posts\FocSlotPost;
use FOC\Classes\Settings\FocSettings;

/**
 * Main plugin controller class.
 *
 * This class handles the core lifecycle operations of the plugin:
 * - Registering WordPress hooks
 * - Handling activation and deactivation routines
 * - Creating and removing plugin-related database tables
 * - Adding the “Settings” link in the Plugins list
 *
 * All plugin-level initializations should be orchestrated through this class.
 */
class FocPlugin
{
    /**
     * Background process classes.
     */
    protected static array $processes = [
        FocBrandSyncProcess::class,
        FocBrandImportProcess::class,
        FocSlotSyncProcess::class,
        FocSlotImportProcess::class,
        FocResetAllDataProcess::class,
    ];

    /**
     * Custom post-type classes.
     */
    protected static array $posts = [
        FocBrandPost::class,
        FocSlotPost::class,
    ];

    /**
     * Service / UI classes that self-register hooks.
     */
    protected static array $services = [
        FocSettings::class,
        FocImport::class,
    ];

    /**
     * Register hooks used by the plugin.
     *
     * Should be called on plugins_loaded.
     */
    public static function init(): void
    {
        // Add the "Settings" link in the plugin list
        add_filter(
            'plugin_action_links_' . plugin_basename(FOC_PLUGIN_FILE),
            [self::class, 'addSettingsLink']
        );

        // Init services (admin UI, settings, ajax, etc.)
        foreach (self::$services as $service) {
            new $service();
        }

        // Init process classes
        foreach (self::$processes as $process) {
            if (method_exists($process, 'instance')) {
                $process::instance();
            }
        }

        // Load custom posts
        add_action('init', function () {
            foreach (self::$posts as $postType) {
                $postType::register();

                if (method_exists($postType, 'registerMetaBoxes')) {
                    $postType::registerMetaBoxes();
                }
            }
        });
    }

    /**
     * Plugin activation hook.
     *
     * Creates all required database tables.
     */
    public static function activate(): void
    {
    }

    /**
     * Plugin deactivation hook.
     *
     * Drops all database tables created by the plugin.
     * WARNING: This removes all plugin data.
     */
    public static function deactivate(): void
    {
    }

    /**
     * Register hooks used by the plugin.
     *
     * Should be called on plugins_loaded.
     */
    public static function addSettingsLink(array $links): array
    {
        // Link to your plugin settings page
        $settings_url = admin_url('options-general.php?page=foc-api-settings');

        $settings_link = '<a href="' . esc_url($settings_url) . '">' . __('Settings') . '</a>';

        array_unshift($links, $settings_link);

        return $links;
    }
}

