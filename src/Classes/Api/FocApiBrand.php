<?php

namespace FOC\Classes\Api;

use FOC\Interfaces\FocApiInterface;
use FOC\Traits\FocApiProxyTrait;

/**
 * API service for fetching casino brands.
 */
class FocApiBrand extends FocApi implements FocApiInterface
{
    use FocApiProxyTrait;

    /**
     * Main API endpoint for brand data.
     */
    protected const string ENDPOINT = '/casino-brands';

    /**
     * API endpoint for fetching select-style options (id => name).
     */
    protected const string ENDPOINT_OPTIONS = '/casino-brands/options';

    /**
     * Constructor.
     */
    public function __construct(string $baseUrl, string $apiToken)
    {
        parent::__construct($baseUrl, $apiToken);
    }
}
