<?php

namespace FOC\Background;

use FOC\Background\Abstracts\FocAbstractImportProcess;
use FOC\Classes\Api\FocApiSlot;
use FOC\Jobs\FocSlotImportJob;
use FOC\Models\FocSlotModel;
use FOC\Traits\FocApiAwareTrait;
use FOC\Traits\FocSingletonTrait;

/**
 * FocSlotImportProcess
 *
 * Background process responsible for importing slot data
 * from the external API in paginated batches.
 *
 * Each queued task represents a single page of API results
 * that is fetched, processed, and persisted to the database.
 */
class FocSlotImportProcess extends FocAbstractImportProcess
{
    use FocSingletonTrait;

    /**
     * Background process action name.
     */
    protected string $action = 'slot_import';

    /**
     * Model class providing fillable attributes and key map.
     */
    protected function modelClass(): string
    {
        return FocSlotModel::class;
    }

    /**
     * Job class whose status will be updated during sync.
     */
    protected function statusJob(): string
    {
        return FocSlotImportJob::class;
    }

    /**
     * Job class to be triggered when the sync is complete.
     */
    protected function nextJob(): string
    {
        return '';
    }

    /**
     * Post type slug.
     */
    protected function postType(): string
    {
        return 'slot';
    }

    /**
     * Meta-key in post-meta.
     */
    protected function metaKey(): string
    {
        return 'slot_id';
    }

    /**
     * Define which API strategy should be used by this process.
     *
     * The returned class must implement {@see FocApiInterface} and represents
     * the concrete API client that will be initialized by {@see FocApiAwareTrait}.
     *
     * This allows the same background process logic to be reused with
     * different API endpoints by simply changing the strategy.
     */
    protected function apiStrategy(): string
    {
        return FocApiSlot::class;
    }
}