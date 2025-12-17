<?php

namespace FOC\Background;

use FOC\Background\Abstracts\FocAbstractAsyncRequest;
use FOC\Services\PostTypeSyncService;
use FOC\Traits\FocSingletonTrait;

/**
 * FocResetAllDataProcess
 *
 * Background process responsible for resetting all plugin data.
 *
 * This process runs asynchronously using the WordPress background processing
 * framework. Currently, it deletes all brand posts but can be extended
 * to remove other plugin-related data such as slots, bonuses, or custom meta.
 *
 * Usage:
 * - Push tasks to the queue for asynchronous execution.
 * - Each task can perform part of the deletion or reset operations.
 */
class FocResetAllDataProcess extends FocAbstractAsyncRequest
{
    use FocSingletonTrait;

    /**
     * Process a single task from the background queue.
     *
     * @return false Always return false to remove the task from the queue
     */
    public function handle(): false
    {
        PostTypeSyncService::deleteByPostTypes(['brand', 'slot']);

        // Task processed, remove from queue
        return false;
    }
}
