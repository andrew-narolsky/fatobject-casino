<?php

/**
 * Plugin Name: FatObject Casino
 * Description: FatObject Casino Plugin
 * Text Domain: foc-casino
 * Version: 1.0
 * Author: SiteForYou
 * License: GPL2
 * Requires PHP: 8.3
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoload classes using Composer autoload
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Plugin version constant
 */
const FOC_PLUGIN_VERSION = '1.0';

/**
 * Main plugin file path constant
 */
const FOC_PLUGIN_FILE = __FILE__;

/**
 * Absolute filesystem path to the plugin root directory.
 *
 * Used for loading plugin assets, templates, and internal files.
 * Always ends with a trailing slash.
 */
define('FOC_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Absolute URL to the plugin root directory.
 *
 * Used for enqueueing plugin assets (CSS, JavaScript, images) and
 * generating public-facing URLs. Always ends with a trailing slash.
 */
define('FOC_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Include the plugin's global functions file
 */
require_once FOC_PLUGIN_PATH . 'functions.php';

/**
 * Initialize plugin hooks (settings link, etc.)
 */
add_action('plugins_loaded', [FOC\Classes\Plugin\FocPlugin::class, 'init']);

/**
 * Register plugin activation and deactivation hooks.
 */
register_activation_hook(FOC_PLUGIN_FILE, [FOC\Classes\Plugin\FocPlugin::class, 'activate']);
register_deactivation_hook(FOC_PLUGIN_FILE, [FOC\Classes\Plugin\FocPlugin::class, 'deactivate']);
