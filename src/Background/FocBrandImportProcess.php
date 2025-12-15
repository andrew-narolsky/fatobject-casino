<?php

namespace FOC\Background;

use FOC\Jobs\FocBrandImportJob;
use FOC\Models\FocBrandModel;
use FOC\Traits\FocBrandApiTrait;
use FOC\Traits\FocSingletonTrait;
use FOC\Traits\FocSyncsPostTypeFromApi;

/**
 * FocBrandImportProcess
 *
 * Background process responsible for importing brand data
 * from the external API in paginated batches.
 *
 * Each queued task represents a single page of API results
 * that is fetched, processed, and persisted to the database.
 */
class FocBrandImportProcess extends FocBackgroundProcess
{
    use FocSingletonTrait, FocBrandApiTrait, FocSyncsPostTypeFromApi;

    /**
     * Background process action name.
     */
    protected string $action = 'brand_import';

    /**
     * Process a single task from the queue.
     *
     * Fetches a page of brand data from the API and updates
     * the corresponding records in the database.
     */
    protected function task($item): false
    {
        // Initialize API
        $apiService = $this->initBrandApi();
        $response = $apiService->getPaginated($item['page']);

        // Sync data to DB
        $this->syncPostTypeFromApiWithMeta(
            $response['data'],
            'brand',
            'brand_id',
            'name',
            'id',
            FocBrandModel::getFillable(),
            FocBrandModel::keyMap()
        );

        // Calculate progress
        $processed = $item['page'];
        $percent = (int) round(($processed / $item['total']) * 100);

        // Update status option
        update_option(FocBrandImportJob::STATUS_OPTION, [
            'status'    => 'running',
            'total'     => $item['total'],
            'processed' => $processed,
            'percent'   => $percent,
        ]);

        // Return false to remove this task from the queue
        return false;
    }

    /**
     * Finalize the background process.
     *
     * Called once all queued tasks have been processed.
     */
    protected function complete(): void
    {
        parent::complete();

        update_option(FocBrandImportJob::STATUS_OPTION, [
            'status'    => 'completed',
            'percent'   => 100,
        ]);
    }
}