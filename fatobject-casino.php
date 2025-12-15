<?php

/**
 * Plugin Name: FatObject Casino
 * Description: FatObject Casino Plugin
 * Version: 1.0
 * Author: SiteForYou
 * License: GPL2
 * Requires PHP: 8.3
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoload classes using Composer autoload.
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main plugin file path constant
 */
const FOC_PLUGIN_FILE = __FILE__;

/**
 * Initialize plugin hooks (settings link, etc.)
 */
add_action('plugins_loaded', [FOC\Classes\Plugin\FocPlugin::class, 'init']);

/**
 * Register plugin activation and deactivation hooks.
 */
register_activation_hook(FOC_PLUGIN_FILE, [FOC\Classes\Plugin\FocPlugin::class, 'activate']);
register_deactivation_hook(FOC_PLUGIN_FILE, [FOC\Classes\Plugin\FocPlugin::class, 'deactivate']);
