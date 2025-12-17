<?php

namespace FOC\Classes\Settings;

/**
 * Plugin's settings class
 *
 * Handles plugin settings registration, sanitization, and retrieval.
 * Provides an admin settings page for configuring the Abstracts URL and API token.
 */
class FocSettings
{
    /**
     * Option key used to store plugin settings in the database.
     */
    public const string OPTION_KEY = 'foc_plugin_settings';

    /**
     * Constructor.
     *
     * Registers admin hooks if running in an admin context.
     */
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'registerSettingsPage']);
            add_action('admin_init', [$this, 'registerSettings']);
        }
    }

    /**
     * Register the settings page in the WordPress admin.
     */
    public function registerSettingsPage(): void
    {
        add_options_page(
                'FOC API Settings',       // Page title
                'FOC API',                // Menu title
                'manage_options',         // Capability
                'foc-api-settings',       // Menu slug
                [$this, 'renderSettingsPage'] // Callback
        );
    }

    /**
     * Register plugin settings and sanitize callback.
     */
    public function registerSettings(): void
    {
        register_setting(
                self::OPTION_KEY,
                self::OPTION_KEY,
                [$this, 'sanitizeSettings']
        );
    }

    /**
     * Render the plugin settings page HTML.
     */
    public function renderSettingsPage(): void
    {
        $options = get_option(self::OPTION_KEY, []);
        ?>
        <div class="wrap">
            <h1>FOC API Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields(self::OPTION_KEY); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="foc_base_url">Base URL</label>
                        </th>
                        <td>
                            <input
                                    type="text"
                                    id="foc_base_url"
                                    name="foc_plugin_settings[base_url]"
                                    value="<?php echo esc_attr($options['base_url'] ?? ''); ?>"
                                    class="regular-text"
                            >
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="foc_api_token">API Token</label>
                        </th>
                        <td>
                            <input
                                    type="text"
                                    id="foc_api_token"
                                    name="foc_plugin_settings[api_token]"
                                    value="<?php echo esc_attr($options['api_token'] ?? ''); ?>"
                                    class="regular-text"
                            >
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Sanitize plugin settings before saving to the database.
     */
    public function sanitizeSettings($input): array
    {
        return [
                'base_url' => sanitize_text_field($input['base_url'] ?? ''),
                'api_token' => sanitize_text_field($input['api_token'] ?? ''),
        ];
    }

    /**
     * Get Abstracts URL from saved plugin settings.
     */
    public function getBaseUrl(): string
    {
        $options = get_option(self::OPTION_KEY, []);
        return $options['base_url'] ?? '';
    }

    /**
     * Get API Token from saved plugin settings.
     */
    public function getApiToken(): string
    {
        $options = get_option(self::OPTION_KEY, []);
        return $options['api_token'] ?? '';
    }
}
