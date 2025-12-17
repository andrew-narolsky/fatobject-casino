<?php

namespace FOC\Background\Abstracts;

use FOC\Services\PostTypeSyncService;
use FOC\Traits\FocApiAwareTrait;

/**
 * Abstract class for sync processes (brand, slot, etc.)
 */
abstract class FocAbstractSyncProcess extends FocAbstractBackgroundProcess
{
    use FocApiAwareTrait;

    /**
     * Process a single task from the queue.
     */
    protected function task($item): false
    {
        $apiService = $this->initApi();
        $items = $apiService->getOptions();

        $statusJob = $this->statusJob();
        update_option($statusJob::STATUS_OPTION, [
            'status' => 'running',
            'total' => count($items),
            'processed' => 0,
            'percent' => 0,
        ]);

        $syncService = new PostTypeSyncService();
        $syncService->syncPostTypeFromApi(
            items: $items,
            postType: $this->postType(),
            metaKey: $this->metaKey(),
        );

        return false;
    }

    /**
     * Finalize the background process and trigger the next job.
     */
    protected function complete(): void
    {
        parent::complete();

        $statusJob = $this->statusJob();
        update_option($statusJob::STATUS_OPTION, [
            'status' => 'completed',
            'percent' => 100,
        ]);

        /** @var class-string $nextJob */
        $nextJob = $this->nextJob();
        if (!$this->is_cancelled() && !$this->is_paused() && $nextJob) {
            $nextJob::handle();
        }
    }
}