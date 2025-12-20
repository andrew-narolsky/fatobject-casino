<?php
$meta = get_post_meta(get_the_ID());

$image = foc_get_meta_value($meta, 'image', esc_url(FOC_PLUGIN_URL . 'assets/img/brand-logo.svg'));
$url = foc_normalize_url(foc_get_meta_value($meta, 'url', null));
$rating = foc_get_meta_value($meta, 'rating', null);
$maxRating = 5;

$volatility = foc_get_meta_value($meta, 'volatility');
$max_profit = foc_get_meta_value($meta, 'max_profit');
$payout_percentage = foc_get_meta_value($meta, 'payout_percentage', '—');
if ($payout_percentage !== '—') {
    $payout_percentage .= '%';
}

$permalink = get_the_permalink();

$software_provider = foc_get_meta_value($meta, 'software_provider', []);
if (is_string($software_provider)) {
    $software_provider = maybe_unserialize($software_provider);
}
?>

<div class="item">
    <a href="<?php echo $permalink; ?>" class="img-block">
        <img src="<?php echo $image; ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
    </a>

    <div class="info-block">
        <a href="<?php echo $permalink; ?>" class="item-title"><?php echo get_the_title(); ?></a>

        <?php if ($rating): ?>
            <div class="rate-block rate-<?php echo floor((float)$rating); ?>">
                <span class="rate"><?php echo $rating; ?>/<?php echo $maxRating; ?></span>
            </div>
        <?php endif; ?>

        <div class="bonus-wrapper">
            <div class="bw-item">
                <span class="item-title"><?php echo __('Volatility', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo foc_snake_to_title($volatility); ?></span>
            </div>
            <div class="bw-item">
                <span class="item-title"><?php echo __('Max Profit', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo $max_profit; ?></span>
            </div>
            <div class="bw-item">
                <span class="item-title"><?php echo __('RTP', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo $payout_percentage; ?></span>
            </div>
        </div>
        <div class="button-wrapper">
            <?php if ($url): ?>
                <a href="<?php echo $url; ?>" class="cas-button"><?php echo __('Play', 'foc-casino'); ?></a>
            <?php endif; ?>
            <a href="<?php echo $permalink; ?>" class="cas-button white"><?php echo __('Review', 'foc-casino'); ?></a>
        </div>

        <?php if ($software_provider): ?>
            <div class="game-provider"><?php echo __('Game provider: ', 'foc-casino'); ?><b><?php echo $software_provider['name']; ?></b></div>
        <?php endif; ?>
    </div>
</div>