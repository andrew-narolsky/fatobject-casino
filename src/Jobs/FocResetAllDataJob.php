<?php

namespace FOC\Jobs;

use FOC\Background\FocResetAllDataProcess;

/**
 * FocResetAllDataJob
 *
 * Job responsible for resetting all plugin data.
 *
 * This job can be used to delete all plugin-related posts, meta-fields,
 * and optionally other resources in the future.
 */
class FocResetAllDataJob extends FocAbstractJob
{
    /**
     * Handle the job execution.
     *
     * Initializes the brand sync background process, adds a task
     * to the queue, and dispatches it for asynchronous processing.
     */
    public static function handle(): void
    {
        $process = FocResetAllDataProcess::instance();
        $process->push_to_queue([]);
        $process->save()->dispatch();
    }
}
