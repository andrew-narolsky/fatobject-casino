<?php

namespace FOC\Classes\Import;

use FOC\Jobs\FocBrandImportJob;
use FOC\Jobs\FocBrandSyncJob;
use FOC\Jobs\FocResetAllDataJob;

/**
 * FocImport
 *
 * Registers and renders the admin import page and handles
 * AJAX-based import actions.
 *
 * This class is responsible for wiring the WordPress admin UI
 * with background import jobs, allowing the import process
 * to run asynchronously without page reloads.
 */
class FocImport
{
    /**
     * Admin page slug.
     */
    public const string PAGE_SLUG = 'foc-import';

    /**
     * Constructor.
     *
     * Registers admin hooks and AJAX handlers.
     */
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'registerPage']);
            add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        }

        // AJAX handlers (logged-in users only)
        add_action('wp_ajax_foc_run_import', [$this, 'ajaxRunImport']);
        add_action('wp_ajax_foc_reset_data', [$this, 'ajaxResetData']);
        add_action('wp_ajax_foc_import_status', [$this, 'ajaxImportStatus']);
        add_action('wp_ajax_foc_clear_import_status', [$this, 'ajaxClearImportStatus']);
    }

    /**
     * Register the import admin page.
     *
     * Adds the import page under the "Tools" menu.
     */
    public function registerPage(): void
    {
        add_management_page(
            'FOC Import',
            'FOC Import',
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderPage']
        );
    }

    /**
     * Enqueue JavaScript assets for the import page.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueueScripts(string $hook): void
    {
        // Only load scripts on our import page
        if ($hook !== 'tools_page_' . self::PAGE_SLUG) {
            return;
        }

        wp_enqueue_script(
            'foc-import-js',
            plugin_dir_url(FOC_PLUGIN_FILE) . 'assets/js/foc-import.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_enqueue_style(
            'foc-import-css',
            plugin_dir_url(FOC_PLUGIN_FILE) . 'assets/css/foc-import.css',
            [],
            '1.0'
        );

        wp_localize_script('foc-import-js', 'FOC_IMPORT', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('foc_import_ajax'),
            'nonce_reset'  => wp_create_nonce('foc_reset_data_ajax'),
        ]);
    }

    /**
     * AJAX import handler.
     *
     * Triggers the background brand synchronization job
     * and returns an immediate JSON response.
     */
    public function ajaxRunImport(): void
    {
        check_ajax_referer('foc_import_ajax', 'nonce');

        FocBrandSyncJob::handle();

        wp_send_json_success([
            'message' => 'Import started successfully!',
        ]);
    }

    /**
     * AJAX handler to reset all brands (delete all posts).
     *
     * This method triggers the background job `FocResetAllDataJob`,
     * which deletes all brand posts asynchronously.
     * Returns a JSON response immediately to indicate the reset has started.
     */
    public function ajaxResetData(): void
    {
        check_ajax_referer('foc_reset_data_ajax', 'nonce_reset');

        FocResetAllDataJob::handle();

        wp_send_json_success([
            'message' => 'Reset process has started!',
        ]);
    }

    /**
     * AJAX handler to get the current import status.
     *
     * Returns the status stored in the options for both Sync and Import tasks.
     * If no status exists, returns `idle` for each task.
     */
    public function ajaxImportStatus(): void
    {
        check_ajax_referer('foc_import_ajax', 'nonce');

        $brandSyncStatus   = get_option(FocBrandSyncJob::STATUS_OPTION)   ?: ['status' => 'idle', 'percent' => 0];
        $brandImportStatus = get_option(FocBrandImportJob::STATUS_OPTION) ?: ['status' => 'idle', 'percent' => 0];

        wp_send_json_success([
            'brandSync'  => $brandSyncStatus,
            'brandImport'=> $brandImportStatus,
        ]);
    }

    /**
     * AJAX handler to clear the import status.
     *
     * Deletes the options for both Sync and Import tasks.
     * Typically called after all tasks are completed.
     */
    public function ajaxClearImportStatus(): void
    {
        check_ajax_referer('foc_import_ajax', 'nonce');

        delete_option(FocBrandSyncJob::STATUS_OPTION);
        delete_option(FocBrandImportJob::STATUS_OPTION);

        wp_send_json_success();
    }

    /**
     * Render the import admin page.
     */
    public function renderPage(): void
    {
        $brandSyncStatus = get_option(FocBrandSyncJob::STATUS_OPTION);
        $brandImportStatus = get_option(FocBrandImportJob::STATUS_OPTION);

        $isSyncRunning = $brandSyncStatus && $brandSyncStatus['status'] === 'running';
        $syncPercent = $brandSyncStatus ? ($brandSyncStatus['percent'] ?? 0) : 0;

        $isImportRunning = $brandImportStatus && $brandImportStatus['status'] === 'running';
        $importPercent = $brandImportStatus ? ($brandImportStatus['percent'] ?? 0) : 0;

        $isAnyRunning = $isSyncRunning || $isImportRunning;
        ?>
        <div class="wrap">
            <h1>FOC Import</h1>

            <div id="foc-import-result" style="margin-top: 20px;"></div>

            <!-- Brand Sync Progress -->
            <div id="foc-sync-progress" class="foc-progress" style="<?= $isSyncRunning || $syncPercent === 100 ? '' : 'display:none;' ?>">
                <div class="foc-progress__bar" style="width: <?= esc_attr($syncPercent) ?>%"></div>
                <span class="foc-progress__label"><?= esc_html($syncPercent) ?>%</span>
                <div class="foc-progress__text">Syncing brands</div>
            </div>

            <!-- Brand Import Progress -->
            <div id="foc-import-progress" class="foc-progress" style="<?= $isImportRunning ? '' : 'display:none;' ?>">
                <div class="foc-progress__bar" style="width: <?= esc_attr($importPercent) ?>%"></div>
                <span class="foc-progress__label"><?= esc_html($importPercent) ?>%</span>
                <div class="foc-progress__text">Importing brands</div>
            </div>

            <p>Click the button below to start the import. The process will run asynchronously.</p>

            <button class="button button-primary" id="foc-import-btn" <?= $isAnyRunning ? 'disabled' : '' ?>>
                <?= $isAnyRunning ? 'Importing...' : 'Start Import' ?>
            </button>

            <button class="button btn-danger" id="foc-reset-btn" <?= $isAnyRunning ? 'disabled' : '' ?>>
                Reset All Data
            </button>
        </div>
        <?php
    }
}
