<?php

namespace FOC\Services;

use WP_Query;

/**
 * FocLoadMoreService
 *
 * Handles the "Load More" functionality for Brand/Slot posts via AJAX.
 *
 * Responsibilities:
 * - Registers AJAX actions for both logged-in and guest users
 * - Queries the next page of Brand/Slot posts
 * - Returns HTML using template rendering
 * - Returns a flag indicating if more pages exist
 */
class FocLoadMoreService
{
    /**
     * Default number of items displayed per page.
     */
    protected const int PER_PAGE = 9;

    /**
     * Default number of page.
     */
    protected const int PAGE = 1;

    public function __construct()
    {
        add_action('wp_ajax_foc_load_more', [$this, 'handleAjax']);
        add_action('wp_ajax_nopriv_foc_load_more', [$this, 'handleAjax']);
    }

    /**
     * AJAX handler for loading more Brand/Slot posts.
     */
    public function handleAjax(): void
    {
        check_ajax_referer('foc_frontend', 'nonce');

        $page     = max(1, (int) ($_POST['page'] ?? 1));
        $perPage = max(1, (int) ($_POST['per_page'] ?? self::PER_PAGE));
        $postType = sanitize_key($_POST['post_type'] ?? '');

        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $perPage,
            'paged'          => $page,
        ];

        // ids
        if (!empty($_POST['ids'])) {
            $ids = array_values(
                array_filter(
                    array_map('intval', explode(',', $_POST['ids']))
                )
            );

            if ($ids) {
                $args['post__in'] = $ids;
                $args['orderby'] = 'post__in';
            }
        }

        // sorting
        if (!empty($_POST['orderby'])) {
            $args['orderby'] = sanitize_text_field($_POST['orderby']);
            $args['order']   = sanitize_text_field($_POST['order'] ?? 'DESC');

            if (in_array($args['orderby'], ['meta_value', 'meta_value_num'], true)) {
                $args['meta_key'] = sanitize_text_field($_POST['meta_key'] ?? '');
            }
        }

        $query = new WP_Query($args);

        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            foc_render('parts/slot-card.php');
        }
        wp_reset_postdata();

        wp_send_json_success([
            'html'     => ob_get_clean(),
            'has_more' => $page < $query->max_num_pages,
        ]);
    }
}
