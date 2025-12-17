<?php

namespace FOC\Background\Abstracts;

use FOC\Services\PostTypeSyncService;
use FOC\Traits\FocApiAwareTrait;

/**
 * Abstract class for paginated import background processes.
 *
 * Handles common logic for fetching pages from an API,
 * syncing data to a custom post-type, and updating the job status.
 */
abstract class FocAbstractImportProcess extends FocAbstractBackgroundProcess
{
    use FocApiAwareTrait;

    /**
     * Model class providing fillable attributes and key map.
     */
    abstract protected function modelClass(): string;

    /**
     * Process a single page of API data.
     */
    protected function task($item): false
    {
        $apiService = $this->initApi();
        $response = $apiService->getPaginated($item['page'] ?? 1);

        $modelClass = $this->modelClass();

        /** @var class-string $modelClass */
        $syncService = new PostTypeSyncService();
        $syncService->syncPostTypeFromApiWithMeta(
            $response['data'] ?? [],
            $this->postType(),
            $this->metaKey(),
            'name',
            'id',
            $modelClass::getFillable(),
            $modelClass::keyMap()
        );

        $processed = $item['page'] ?? 1;
        $percent = (int)round(($processed / ($item['total'] ?? 1)) * 100);

        update_option($this->statusJob()::STATUS_OPTION, [
            'status' => 'running',
            'total' => $item['total'] ?? 0,
            'processed' => $processed,
            'percent' => $percent,
        ]);

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
