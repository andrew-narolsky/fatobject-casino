<?php

namespace FOC\Background;

use FOC\Traits\FocSingletonTrait;

/**
 * FocResetAllDataProcess
 *
 * Background process responsible for resetting all plugin data.
 *
 * This process runs asynchronously using the WordPress background processing
 * framework. Currently, it deletes all brand posts but can be extended
 * to remove other plugin-related data such as slots, bonuses, or custom meta.
 *
 * Usage:
 * - Push tasks to the queue for asynchronous execution.
 * - Each task can perform part of the deletion or reset operations.
 */
class FocResetAllDataProcess extends FocBackgroundProcess
{
    use FocSingletonTrait;

    /**
     * Process a single task from the background queue.
     *
     * @return false Always return false to remove the task from the queue
     */
    protected function task($item): false
    {
        // Delete all brand posts
        $brandPosts = get_posts([
            'post_type'   => 'brand',
            'numberposts' => -1,
            'fields'      => 'ids',
        ]);

        foreach ($brandPosts as $postId) {
            wp_delete_post($postId, true);
        }

        // Task processed, remove from queue
        return false;
    }
}
