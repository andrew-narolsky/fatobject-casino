<?php

namespace FOC\Background;

use FOC\Background\Abstracts\FocAbstractSyncProcess;
use FOC\Classes\Api\FocApiSlot;
use FOC\Jobs\FocSlotImportJob;
use FOC\Jobs\FocSlotSyncJob;
use FOC\Traits\FocApiAwareTrait;
use FOC\Traits\FocSingletonTrait;

/**
 * FocSlotSyncProcess
 *
 * Background process responsible for fetching all slots from the API
 * and syncing them with the local database.
 *
 * Once the sync is complete, it automatically triggers the paginated
 */
class FocSlotSyncProcess extends FocAbstractSyncProcess
{
    use FocSingletonTrait;

    /**
     * Background process action name.
     */
    protected string $action = 'slot_sync';

    /**
     * Job class whose status will be updated during sync.
     */
    protected function statusJob(): string
    {
        return FocSlotSyncJob::class;
    }

    /**
     * Job class to be triggered when the sync is complete.
     */
    protected function nextJob(): string
    {
        return FocSlotImportJob::class;
    }

    /**
     * Post type slug.
     */
    protected function postType(): string
    {
        return 'slot';
    }

    /**
     * Meta-key to match API items to posts.
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