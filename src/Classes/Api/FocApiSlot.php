<?php

namespace FOC\Classes\Api;

use FOC\Classes\Api\Abstracts\FocAbstractApi;
use FOC\Interfaces\FocApiInterface;

/**
 * API service for fetching casino slots.
 */
class FocApiSlot extends FocAbstractApi implements FocApiInterface
{
    /**
     * Main API endpoint for slot data.
     */
    protected static function endpoint(): string
    {
        return '/slots';
    }
}