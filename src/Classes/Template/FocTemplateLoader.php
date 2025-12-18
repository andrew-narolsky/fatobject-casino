<?php

namespace FOC\Classes\Template;

/**
 * Template loader helper.
 *
 * This class is responsible for locating and rendering plugin templates,
 * allowing themes to override plugin templates by providing a file
 * with the same path in the theme directory.
 *
 * Features:
 * - `locate()`: finds the first available template in the theme or plugin.
 * - `render()`: loads a template file and optionally extracts variables
 *   into the template scope.
 *
 * Usage:
 *   - FocTemplateLoader::locate('single-brand.php') → returns a path to template
 *   - FocTemplateLoader::render('parts/sidebar-brand-card.php', ['var' => $value])
 *     → loads the template and makes $var available inside it
 *
 * This approach separates template markup from plugin logic
 * and enables theme-level customizations without modifying the plugin.
 */
class FocTemplateLoader
{
    /**
     * Locate a template, allowing themes to override plugin templates.
     */
    public static function locate(string $template): string
    {
        $themeTemplate = locate_template([
            'fatobject-casino/' . $template,
            $template,
        ]);

        if ($themeTemplate) {
            return $themeTemplate;
        }

        return FOC_PLUGIN_PATH . 'templates/' . $template;
    }

    /**
     * Load a template part with optional variables.
     */
    public static function render(string $template, array $args = []): void
    {
        $file = self::locate($template);

        if (!file_exists($file)) {
            error_log("FocTemplateLoader: Template not found: {$template}");
            return;
        }

        if ($args) {
            extract($args, EXTR_SKIP);
        }

        require $file;
    }
}