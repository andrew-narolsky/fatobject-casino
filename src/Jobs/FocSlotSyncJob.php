<?php

namespace FOC\Jobs;

use FOC\Background\FocSlotSyncProcess;
use FOC\Jobs\Abstracts\FocAbstractSyncJob;

/**
 * FocSlotSyncJob
 *
 * Dispatches the background process responsible for synchronizing
 * slot data from the external API into the local database.
 *
 * This job acts as a lightweight entry point that initializes
 * the background process, pushes the initial payload to the queue,
 * and triggers asynchronous execution via WordPress background processing.
 */
class FocSlotSyncJob extends FocAbstractSyncJob
{
    /**
     * Status option name stored in the database.
     *
     * Used to track the progress of the slot synchronization task.
     * The option stores an array with keys like:
     *  - status: 'queued' | 'running' | 'completed'
     *  - percent: integer (progress percentage)
     *  - processed: integer (items processed)
     *  - total: integer (total items)
     */
    public const string STATUS_OPTION = 'foc_slot_sync_status';

    /**
     * Handle the job execution.
     *
     * Initializes the slot sync background process, adds a task
     * to the queue, and dispatches it for asynchronous processing.
     */
    public static function handle(): void
    {
        static::dispatchProcess(FocSlotSyncProcess::class);
    }
}
