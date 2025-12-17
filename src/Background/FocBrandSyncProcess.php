<?php

namespace FOC\Background;

use FOC\Background\Abstracts\FocAbstractSyncProcess;
use FOC\Classes\Api\FocApiBrand;
use FOC\Jobs\FocBrandImportJob;
use FOC\Jobs\FocBrandSyncJob;
use FOC\Traits\FocApiAwareTrait;
use FOC\Traits\FocSingletonTrait;

/**
 * FocBrandSyncProcess
 *
 * Background process responsible for fetching all brands from the API
 * and syncing them with the local database.
 *
 * Once the sync is complete, it automatically triggers the paginated
 */
class FocBrandSyncProcess extends FocAbstractSyncProcess
{
    use FocSingletonTrait;

    /**
     * Background process action name.
     */
    protected string $action = 'brand_sync';

    /**
     * Job class whose status will be updated during sync.
     */
    protected function statusJob(): string
    {
        return FocBrandSyncJob::class;
    }

    /**
     * Job class to be triggered when the sync is complete.
     */
    protected function nextJob(): string
    {
        return FocBrandImportJob::class;
    }

    /**
     * Post type slug.
     */
    protected function postType(): string
    {
        return 'brand';
    }

    /**
     * Meta-key to match API items to posts.
     */
    protected function metaKey(): string
    {
        return 'brand_id';
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
        return FocApiBrand::class;
    }
}