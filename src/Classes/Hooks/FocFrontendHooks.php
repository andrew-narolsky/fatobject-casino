<?php

namespace FOC\Classes\Hooks;

use FOC\Classes\Template\FocTemplateLoader;

/**
 * Frontend hooks renderer.
 *
 * This class registers and renders default frontend output blocks
 * for plugin templates using WordPress action hooks.
 *
 * It provides a clean separation between layout (templates)
 * and presentation logic (HTML output), allowing themes or other
 * plugins to override or replace individual blocks via hooks.
 *
 * Typical usage:
 * - Templates call `do_action()` for specific layout sections
 * - This class attaches default renderers to those actions
 *
 * All methods output HTML directly.
 */
class FocFrontendHooks
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('foc_sidebar_brand_card', [$this, 'renderSidebarBrandCard']);
        add_action('foc_sidebar_slot_card', [$this, 'renderSidebarSlotCard']);
        add_action('foc_breadcrumbs', [$this, 'renderBreadcrumbs']);
        add_action('foc_main_title', [$this, 'renderTitle']);
        add_action('foc_main_content', [$this, 'renderContent']);
    }

    /**
     * Render sidebar brand card template.
     */
    public function renderSidebarBrandCard(): void
    {
        FocTemplateLoader::render('parts/sidebar-brand-card.php');
    }

    /**
     * Render sidebar slot card template.
     */
    public function renderSidebarSlotCard(): void
    {
        FocTemplateLoader::render('parts/sidebar-slot-card.php');
    }

    /**
     * Render a breadcrumbs' template.
     */
    public function renderBreadcrumbs(): void
    {
        FocTemplateLoader::render('parts/breadcrumbs.php');
    }

    /**
     * Render the main title template.
     */
    public function renderTitle(): void
    {
        FocTemplateLoader::render('parts/title.php');
    }

    /**
     * Render main content template.
     */
    public function renderContent(): void
    {
        FocTemplateLoader::render('parts/content.php');
    }
}
