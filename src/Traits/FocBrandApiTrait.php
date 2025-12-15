<?php

namespace FOC\Traits;

use FOC\Classes\Api\FocApiBrand;
use FOC\Classes\Settings\FocSettings;
use FOC\Services\FocApiService;

/**
 * FocBrandApiTrait
 *
 * Provides a reusable method to initialize and return
 * a configured brand API service.
 *
 * This trait is intended to be used by background processes
 * or other classes that need to communicate with the brand API,
 * avoiding code duplication for API setup.
 */
trait FocBrandApiTrait
{
    /**
     * Initialize and return the brand API service.
     */
    protected function initBrandApi(): FocApiService
    {
        $settings = new FocSettings();
        $apiService = new FocApiService();
        $apiService->setStrategy(new FocApiBrand(
            $settings->getBaseUrl(),
            $settings->getApiToken()
        ));

        return $apiService;
    }
}
