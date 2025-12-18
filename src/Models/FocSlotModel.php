<?php

namespace FOC\Models;

use FOC\Models\Abstracts\FocAbstractModel;

/**
 * FocSlotModel
 *
 * Represents a Slot entity synchronized from the external API.
 *
 * This model acts as a mapping layer between the API response
 * and the WordPress custom post type `slot`.
 *
 * API fields may be returned in camelCase format, while WordPress
 * meta-keys follow snake_case naming. The model provides a key map
 * to normalize API fields before they are stored as post-meta.
 *
 * The list of fillable attributes defines which mapped meta-fields
 * are allowed to be created or updated during the import process.
 *
 * Actual persistence (post-creation, meta updates) is handled
 * by higher-level services or import jobs.
 */
class FocSlotModel extends FocAbstractModel
{
    /**
     * Maps API response keys (camelCase) to WordPress meta-keys (snake_case).
     *
     * This mapping is applied when normalizing slot data received
     * from the API before saving it to the `slot` custom post-type.
     */
    public static function keyMap(): array
    {
        return [
            'payoutPercentage' => 'payout_percentage',
            'minBet' => 'min_bet',
            'maxBet' => 'max_bet',
            'maxProfit' => 'max_profit',
            'hasJackpot' => 'has_jackpot',
            'hasProgressiveSlot' => 'has_progressive_slot',
            'hasAutoPlay' => 'has_auto_play',
            'hasBonusBuy' => 'has_bonus_buy',
            'isMegaways' => 'is_mega_ways',
            'hasHoldAndWin' => 'has_hold_and_win',
            'softwareProvider' => [
                'meta' => 'software_provider',
                'value' => null,
            ],
        ];
    }

    /**
     * Defines repeater meta-fields structure.
     */
    public static function getRepeaters(): array
    {
        return [
            'software_provider' => [
                'name',
                'website',
                'image',
            ],
        ];
    }

    /**
     * Returns the list of allowed WordPress meta-fields
     * for the `slot` custom post type.
     *
     * Only these fields (after API key mapping) will be
     * persisted during slot synchronization and import.
     */
    public static function getFillable(): array
    {
        return [
            'slot_id',
            'url',
            'image',
            'payout_percentage',
            'rows',
            'reels',
            'paylines',
            'min_bet',
            'max_bet',
            'max_profit',
            'volatility',
            'has_jackpot',
            'has_progressive_slot',
            'has_auto_play',
            'has_bonus_buy',
            'is_mega_ways',
            'has_hold_and_win',
            'software_provider'
        ];
    }

    /**
     * Returns the list of fields that should be displayed as read-only / disabled in the meta-box.
     */
    public static function getDisabledFields(): array
    {
        return [
            'slot_id',
        ];
    }

    /**
     * Extra fields not coming from API, used internally in admin
     */
    public static function getExtraFields(): array
    {
        return [
            'rating',
        ];
    }
}
