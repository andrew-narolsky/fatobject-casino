<?php

namespace FOC\Jobs;

use FOC\Background\FocBrandImportProcess;

/**
 * FocBrandImportJob
 *
 * Dispatches the background process responsible for importing
 * brand data into the system in paginated batches.
 *
 * The job calculates the total number of brands stored locally,
 * splits the import process into pages, and enqueues each page
 * as a separate background task for asynchronous processing.
 */
class FocBrandImportJob extends FocAbstractJob
{
    /**
     * Status option name stored in the database.
     *
     * Used to track the progress of the brand synchronization task.
     * The option stores an array with keys like:
     *  - status: 'queued' | 'running' | 'completed'
     *  - percent: integer (progress percentage)
     *  - processed: integer (items processed)
     *  - total: integer (total items)
     */
    public const string STATUS_OPTION = 'foc_brand_import_status';

    /**
     * Handle the job execution.
     *
     * Initializes the brand sync background process, adds a task
     * to the queue, and dispatches it for asynchronous processing.
     */
    public static function handle(): void
    {
        update_option(self::STATUS_OPTION, [
            'status' => 'queued',
        ]);

        $perPage = 10;
        $count = (int) (wp_count_posts('brand')->publish ?? 0);

        // Skip import if there are no brands to process
        if ($count === 0) {
            error_log('FocBrandImportJob skipped: no brands found.');
            return;
        }
        $totalPages = (int) ceil($count / $perPage);

        $process = new FocBrandImportProcess();

        // Enqueue a background task for each page
        for ($page = 1; $page <= $totalPages; $page++) {
            $process->push_to_queue([
                'page' => $page,
                'total' => $totalPages,
            ]);
        }

        // Save and dispatch queue asynchronously
        $process->save()->dispatch();
    }
}
