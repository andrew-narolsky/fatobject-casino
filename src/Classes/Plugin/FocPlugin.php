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
     * Hour of day (24h format) when the daily import cron should run.
     *
     * Uses WordPress timezone (see Settings → General).
     */
    private const int CRON_HOUR = 0;

    /**
     * Minute of the hour when the daily import cron should run.
     *
     * Combined with CRON_HOUR to calculate the next execution time.
     */
    private const int CRON_MINUTE = 0;

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
        if (wp_next_scheduled(FocImport::CRON_HOOK)) {
            return;
        }

        $timestamp = self::getNextRunTimestamp();

        wp_schedule_event(
            $timestamp,
            'daily',
            FocImport::CRON_HOOK
        );
    }

    /**
     * Plugin deactivation hook.
     *
     * Drops all database tables created by the plugin.
     * WARNING: This removes all plugin data.
     */
    public static function deactivate(): void
    {
        // Clear daily import cron
        wp_clear_scheduled_hook(FocImport::CRON_HOOK);
    }

    /**
     * Add a Settings link to the plugins list.
     */
    public static function addSettingsLink(array $links): array
    {
        // Link to your plugin settings page
        $settings_url = admin_url('options-general.php?page=foc-api-settings');

        $settings_link = '<a href="' . esc_url($settings_url) . '">' . __('Settings') . '</a>';

        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Calculate the timestamp for the next scheduled cron run.
     *
     * Determines the ближайчий запуск щоденного імпорту на основі
     * CRON_HOUR та CRON_MINUTE, використовуючи timezone WordPress.
     *
     * - If the scheduled time today has not yet passed, it will run today
     * - Otherwise, it will be scheduled for tomorrow
     */
    private static function getNextRunTimestamp(): int
    {
        $now = current_time('timestamp');

        $run = strtotime(
            sprintf('today %02d:%02d', self::CRON_HOUR, self::CRON_MINUTE),
            $now
        );

        if ($run <= $now) {
            $run = strtotime(
                sprintf('tomorrow %02d:%02d', self::CRON_HOUR, self::CRON_MINUTE),
                $now
            );
        }

        return $run;
    }
}

