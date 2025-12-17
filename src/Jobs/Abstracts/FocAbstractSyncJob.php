<?php

namespace FOC\Jobs\Abstracts;

/**
 * FocAbstractSyncJob
 *
 * Abstracts abstract class for synchronization jobs.
 *
 * Extends FocAbstractJob and provides a reusable method
 * to dispatch a background process for syncing data.
 * This helps to eliminate duplicated code in individual job classes.
 */
abstract class FocAbstractSyncJob extends FocAbstractJob
{
    /**
     * Dispatch a background process for the job.
     *
     * This method updates the job status to 'queued', initializes
     * the given process class, pushes an initial task to the queue,
     * and dispatches it for asynchronous execution.
     *
     * @param string $processClass Fully qualified class name of the process to dispatch.
     */
    protected static function dispatchProcess(string $processClass): void
    {
        if (!class_exists($processClass)) {
            error_log("Process class {$processClass} not found");
            return;
        }

        // Set job status to queued
        update_option(static::STATUS_OPTION, [
            'status' => 'queued',
        ]);

        // Initialize the process, push a task, and dispatch it
        $process = $processClass::instance();
        $process->push_to_queue([]);
        $process->save()->dispatch();
    }
}
