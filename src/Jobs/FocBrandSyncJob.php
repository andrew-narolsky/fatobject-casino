<?php

namespace FOC\Jobs;

use FOC\Background\FocBrandSyncProcess;

/**
 * FocBrandSyncJob
 *
 * Dispatches the background process responsible for synchronizing
 * brand data from the external API into the local database.
 *
 * This job acts as a lightweight entry point that initializes
 * the background process, pushes the initial payload to the queue,
 * and triggers asynchronous execution via WordPress background processing.
 */
class FocBrandSyncJob extends FocAbstractJob
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
    public const string STATUS_OPTION = 'foc_brand_sync_status';

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

        $process = FocBrandSyncProcess::instance();
        $process->push_to_queue([]);
        $process->save()->dispatch();
    }
}
