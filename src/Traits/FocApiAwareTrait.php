<?php

namespace FOC\Traits;

use FOC\Classes\Settings\FocSettings;
use FOC\Services\FocApiService;

/**
 * FocApiAwareTrait
 *
 * Provides a reusable way to initialize an API service
 * using a concrete API strategy defined by the consuming class.
 */
trait FocApiAwareTrait
{
    /**
     * Must return the API strategy class name.
     */
    abstract protected function apiStrategy(): string;

    /**
     * Initialize and return the API service with the given strategy.
     */
    protected function initApi(): FocApiService
    {
        $settings = new FocSettings();
        $apiService = new FocApiService();

        $strategyClass = $this->apiStrategy();

        $apiService->setStrategy(
            new $strategyClass(
                $settings->getBaseUrl(),
                $settings->getApiToken()
            )
        );

        return $apiService;
    }
}