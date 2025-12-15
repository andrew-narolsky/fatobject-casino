<?php

namespace FOC\Background;

use FOC\Jobs\FocBrandImportJob;
use FOC\Jobs\FocBrandSyncJob;
use FOC\Traits\FocBrandApiTrait;
use FOC\Traits\FocSingletonTrait;
use FOC\Traits\FocSyncsPostTypeFromApi;

/**
 * FocBrandSyncProcess
 *
 * Background process responsible for fetching all brands from the API
 * and syncing them with the local database.
 *
 * Once the sync is complete, it automatically triggers the paginated
 */
class FocBrandSyncProcess extends FocBackgroundProcess
{
    use FocSingletonTrait, FocBrandApiTrait, FocSyncsPostTypeFromApi;

    /**
     * Background process action name.
     */
    protected string $action = 'brand_sync';

    /**
     * Process a single task from the queue.
     *
     * Fetches a page of brand data from the API and updates
     * the corresponding records in the database.
     */
    protected function task($item): false
    {
        $apiService = $this->initBrandApi();
        $brands = $apiService->getOptions();

        update_option(FocBrandSyncJob::STATUS_OPTION, [
            'status'    => 'running',
            'total'     => count($brands),
            'processed' => 0,
            'percent'   => 0,
        ]);

        $this->syncPostTypeFromApi(
            items: $brands,
            postType: 'brand',
            metaKey: 'brand_id',
        );

        // Task processed, remove from queue
        return false;
    }

    /**
     * Finalize the background process.
     *
     * Called once all queued tasks have been processed.
     * Automatically triggers {@see FocBrandImportJob} unless
     * the process is canceled or paused.
     */
    protected function complete(): void
    {
        parent::complete();

        update_option(FocBrandSyncJob::STATUS_OPTION, [
            'status'    => 'completed',
            'percent'   => 100,
        ]);

        if (!$this->is_cancelled() && !$this->is_paused()) {
            FocBrandImportJob::handle();
        }
    }
}