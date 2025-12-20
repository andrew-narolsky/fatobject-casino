<?php
/**
 * @var int $pages
 * @var array $ids
 * @var string $orderby
 * @var string $order
 * @var string $meta_key
 */

$postType = 'slot';

$args = [
        'post_type' => 'slot',
        'posts_per_page' => $pages,
        'paged' => 1,
];

if (!empty($ids)) {
    $args['post__in'] = $ids;
    $args['orderby'] = 'post__in';
}

if (!empty($orderby)) {
    $args['orderby'] = $orderby;
    $args['order'] = $order;
}

if ($orderby === 'meta_value' || $orderby === 'meta_value_num') {
    $args['meta_key'] = $meta_key;
}

$query = new WP_Query($args);
?>

<div class="foc-casino">
    <div class="container">
        <div class="foc-casino__best-slots"
             data-per-page="<?php echo esc_attr($pages); ?>"
             data-post-type="slot"
             data-page="1"
             data-ids="<?php echo esc_attr(implode(',', $ids)); ?>"
             data-orderby="<?php echo esc_attr($orderby); ?>"
             data-order="<?php echo esc_attr($order); ?>"
             data-meta-key="<?php echo esc_attr($meta_key); ?>">

            <div class="best-slots-wrapper">
                <?php
                while ($query->have_posts()) {
                    $query->the_post();
                    foc_render('parts/slot-card.php');
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