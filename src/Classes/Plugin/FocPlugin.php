<?php

namespace FOC\Classes\Plugin;

use FOC\Background\FocBrandImportProcess;
use FOC\Background\FocBrandSyncProcess;
use FOC\Background\FocResetAllDataProcess;
use FOC\Background\FocSlotImportProcess;
use FOC\Background\FocSlotSyncProcess;
use FOC\Classes\Hooks\FocFrontendHooks;
use FOC\Classes\Import\FocImport;
use FOC\Classes\Posts\FocBrandPost;
use FOC\Classes\Posts\FocSlotPost;
use FOC\Classes\Settings\FocSettings;
use FOC\Classes\Shortcodes\FocBrandBonusesShortcode;
use FOC\Classes\Shortcodes\FocBrandGamesShortcode;
use FOC\Classes\Shortcodes\FocBrandListShortcode;
use FOC\Classes\Shortcodes\FocBrandPaymentSystemsShortcode;
use FOC\Classes\Shortcodes\FocBrandSoftwareProvidersShortcode;
use FOC\Classes\Shortcodes\FocSlotListShortcode;
use FOC\Classes\Template\FocTemplateLoader;
use FOC\Services\FocLoadMoreService;

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
     * Shortcodes classes.
     */
    protected static array $shortcodes = [
        FocBrandBonusesShortcode::class,
        FocBrandPaymentSystemsShortcode::class,
        FocBrandSoftwareProvidersShortcode::class,
        FocBrandGamesShortcode::class,
        FocSlotListShortcode::class,
        FocBrandListShortcode::class,
    ];

    /**
     * Service / UI classes that self-register hooks.
     */
    protected static array $services = [
        FocSettings::class,
        FocImport::class,
        FocFrontendHooks::class,
        FocLoadMoreService::class,
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
            foreach (self::$shortcodes as $shortcode) {
                $shortcode::register();
            }
        });

        // Load shortcodes
        add_action('init', function () {
            foreach (self::$posts as $postType) {
                $postType::register();

                if (method_exists($postType, 'registerMetaBoxes')) {
                    $postType::registerMetaBoxes();
                }
            }
        });

        // Load default templates
        add_filter('template_include', function ($template) {
            $templates = [
                'brand' => 'single-brand.php',
                'slot' => 'single-slot.php',
            ];

            if (is_singular(array_keys($templates))) {
                $postType = get_post_type();
                return FocTemplateLoader::locate($templates[$postType]);
            }

            return $template;
        });

        // Load assets
        add_action('wp_enqueue_scripts', [self::class, 'enqueueAssets']);
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

    /**
     * Enqueue frontend styles and scripts for plugin templates.
     *
     * Loads CSS and JavaScript assets required by the plugin on the frontend.
     * Assets are conditionally enqueued only on supported custom post types
     * (e.g. "brand" and "slot") to avoid unnecessary loading on other pages.
     *
     * This method is intended to be hooked into `wp_enqueue_scripts`.
     */
    public static function enqueueAssets(): void
    {
        /**
         * Uncomment the condition below if you want to load frontend assets
         * ONLY on single Brand and Slot pages.
         *
         * By default, assets will be enqueued globally across the entire site.
         */
        /*
        if (!is_singular(['brand', 'slot'])) {
            return;
        }
        */

        $version = defined('FOC_PLUGIN_VERSION')
            ? FOC_PLUGIN_VERSION
            : filemtime(FOC_PLUGIN_PATH . 'assets/css/foc-frontend.css');

        wp_enqueue_style(
            'foc-frontend',
            FOC_PLUGIN_URL . 'assets/css/foc-frontend.css',
            [],
            $version
        );

        wp_enqueue_script(
            'foc-frontend',
            FOC_PLUGIN_URL . 'assets/js/foc-frontend.js',
            [],
            $version,
            true
        );

        wp_localize_script(
            'foc-frontend',
            'FOC_FRONTEND',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('foc_frontend'),
            ]
        );
    }
}

