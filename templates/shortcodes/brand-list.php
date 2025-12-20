<?php
/**
 * @var int $per_page
 * @var int $page
 * @var array $ids
 * @var string $orderby
 * @var string $order
 * @var string $meta_key
 * @var string $post_type
 */

$args  = foc_build_query_args([
        'post_type' => $post_type,
        'per_page'  => $per_page,
        'page'      => $page,
        'ids'       => $ids,
        'orderby'   => $orderby,
        'order'     => $order,
        'meta_key'  => $meta_key,
]);

$query = new WP_Query($args);
?>

<div class="foc-casino">
    <div class="container">
        <div class="foc-casino__best-brands"
             data-per-page="<?php echo esc_attr($per_page); ?>"
             data-post-type="<?php echo esc_attr($post_type); ?>"
             data-page="<?php echo esc_attr($page); ?>"
             data-ids="<?php echo esc_attr(implode(',', $ids ?: [])); ?>"
             data-orderby="<?php echo esc_attr($orderby ?: ''); ?>"
             data-order="<?php echo esc_attr($order ?: ''); ?>"
             data-meta-key="<?php echo esc_attr($meta_key ?: ''); ?>">

            <div class="best-brands-wrapper">
                <?php
                foreach ($query->posts as $index => $post) {
                    $query->the_post();
                    foc_render('parts/' . $post_type . '-card.php', [
                            'page' => $page,
                            'per_page' => $per_page,
                            'index' => $index,
                    ]);
                }
                wp_reset_postdata();
                ?>
            </div>

            <?php if ($query->max_num_pages > 1): ?>
                <div class="show-more-block">
                    <button class="show-more"><?php echo __('Show more', 'foc-casino'); ?></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>