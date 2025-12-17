<?php

namespace FOC\Classes\Import;

use FOC\Jobs\FocBrandImportJob;
use FOC\Jobs\FocBrandSyncJob;
use FOC\Jobs\FocResetAllDataJob;
use FOC\Jobs\FocSlotImportJob;
use FOC\Jobs\FocSlotSyncJob;

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
                'nonce' => wp_create_nonce('foc_import_ajax'),
                'nonce_reset' => wp_create_nonce('foc_reset_data_ajax'),
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
                'message' => 'All data has been successfully deleted!',
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

        $response = [];

        foreach ($this->getTasks() as $task) {
            $response[$task['responseKey']] = $this->getTaskStatus($task['job']);
        }

        wp_send_json_success($response);
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

        foreach ($this->getTasks() as $task) {
            delete_option($task['job']::STATUS_OPTION);
        }

        wp_send_json_success();
    }

    /**
     * Render the import admin page.
     */
    public function renderPage(): void
    {
        $tasks = $this->getTasks();

        $statuses = [];
        $isAnyRunning = false;

        foreach ($tasks as $key => $task) {
            $status = $this->getTaskStatus($task['job']);

            $statuses[$key] = [
                    'label' => $task['label'],
                    'status' => $status['status'],
                    'percent' => $status['percent'],
                    'running' => $status['status'] === 'running',
            ];

            if ($status['status'] === 'running') {
                $isAnyRunning = true;
            }
        }
        ?>
        <div class="wrap">
            <h1>FOC Import</h1>

            <div id="foc-import-result" style="margin-top: 20px;"></div>

            <?php foreach ($statuses as $key => $task): ?>
                <?php
                $visible = $task['running'] || $task['percent'] === 100;
                ?>
                <div id="foc-<?php echo esc_attr($key); ?>-progress" class="foc-progress"
                     style="<?php echo $visible ? '' : 'display:none;' ?>">
                    <div class="foc-progress__bar" style="width: <?php echo esc_attr($task['percent']); ?>%"></div>
                    <span class="foc-progress__label">
                    <?php echo esc_html($task['percent']); ?>%
                </span>

                    <div class="foc-progress__text">
                        <?php echo esc_html($task['label']); ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <p>Click the button below to start the import. The process will run asynchronously.</p>

            <button class="button button-primary" id="foc-import-btn" <?php echo $isAnyRunning ? 'disabled' : ''; ?>>
                <?php echo $isAnyRunning ? 'Importing...' : 'Start Import'; ?>
            </button>

            <button class="button btn-danger" id="foc-reset-btn" <?php echo $isAnyRunning ? 'disabled' : ''; ?>>
                Reset All Data
            </button>
        </div>
        <?php
    }

    /**
     * Returns the list of import/sync tasks used by the admin UI.
     *
     * Each task defines:
     ** - a unique key used for DOM IDs and JS bindings
     ** - the Job class responsible for the task
     ** - a human-readable label displayed in the progress UI
     *
     * Adding a new task (e.g., bonuses) only requires extending this configuration.
     */
    private function getTasks(): array
    {
        return [
                'brand-sync' => [
                        'job' => FocBrandSyncJob::class,
                        'label' => 'Syncing brands',
                        'responseKey' => 'brandSync',
                ],
                'brand-import' => [
                        'job' => FocBrandImportJob::class,
                        'label' => 'Importing brands',
                        'responseKey' => 'brandImport',
                ],
                'slot-sync' => [
                        'job' => FocSlotSyncJob::class,
                        'label' => 'Syncing slots',
                        'responseKey' => 'slotSync',
                ],
                'slot-import' => [
                        'job' => FocSlotImportJob::class,
                        'label' => 'Importing slots',
                        'responseKey' => 'slotImport',
                ],
        ];
    }

    /**
     * Retrieves the current status of a given Job.
     *
     * The status is stored in WordPress options by the background job itself.
     * If no status is found, the task is considered idle with 0% progress.
     */
    private function getTaskStatus(string $jobClass): array
    {
        return get_option($jobClass::STATUS_OPTION) ?: $this->emptyStatus();
    }

    /**
     * Get a default empty task status.
     *
     * Used when no status option exists in the database.
     * Ensures a consistent status structure for all tasks.
     */
    private function emptyStatus(): array
    {
        return [
                'status' => 'idle',
                'percent' => 0,
        ];
    }
}
