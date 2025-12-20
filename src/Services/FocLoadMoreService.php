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

        $config = [
            'post_type' => sanitize_key($_POST['post_type'] ?? ''),
            'page'      => max(1, (int) ($_POST['page'] ?? self::PAGE)),
            'per_page'  => max(1, (int) ($_POST['per_page'] ?? self::PER_PAGE)),
            'orderby'   => sanitize_text_field($_POST['orderby'] ?? ''),
            'order'     => sanitize_text_field($_POST['order'] ?? 'DESC'),
            'meta_key'  => sanitize_text_field($_POST['meta_key'] ?? ''),
            'ids'       => [],
        ];

        if (!empty($_POST['ids'])) {
            $config['ids'] = array_values(
                array_filter(
                    array_map('intval', explode(',', $_POST['ids']))
                )
            );
        }

        $query = new WP_Query(foc_build_query_args($config));

        ob_start();

        foreach ($query->posts as $index => $post) {
            $query->the_post();
            foc_render('parts/' . $config['post_type'] . '-card.php', [
                'page'     => $config['page'],
                'per_page' => $config['per_page'],
                'index'    => $index,
            ]);
        }
        wp_reset_postdata();

        wp_send_json_success([
            'html'     => ob_get_clean(),
            'has_more' => $config['page'] < $query->max_num_pages,
        ]);
    }
}
