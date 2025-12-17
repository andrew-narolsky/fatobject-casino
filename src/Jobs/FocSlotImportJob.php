<?php

namespace FOC\Jobs;

use FOC\Background\FocSlotImportProcess;
use FOC\Jobs\Abstracts\FocAbstractImportJob;

/**
 * FocSlotImportJob
 *
 * Dispatches the background process responsible for importing
 * slot data into the system in paginated batches.
 *
 * The job calculates the total number of slots stored locally,
 * splits the import process into pages, and enqueues each page
 * as a separate background task for asynchronous processing.
 */
class FocSlotImportJob extends FocAbstractImportJob
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
    public const string STATUS_OPTION = 'foc_slot_import_status';

    /**
     * Post type to be imported.
     */
    protected static function postType(): string
    {
        return 'slot';
    }

    /**
     * Background process class.
     */
    protected static function processClass(): string
    {
        return FocSlotImportProcess::class;
    }
}
