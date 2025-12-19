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

$rows = foc_get_meta_value($meta, 'rows');
$min_bet = foc_get_meta_value($meta, 'min_bet');
$paylines = foc_get_meta_value($meta, 'paylines');
$reels = foc_get_meta_value($meta, 'reels');
?>

<div class="slot-card">
    <?php if ($rating): ?>
        <div class="rate-block rate-<?php echo floor((float)$rating); ?>">
            <span class="rate"><?php echo $rating; ?>/<?php echo $maxRating; ?></span>
        </div>
    <?php endif; ?>

    <div class="game-provider">Game provider: <b>Play'n Go</b></div>

    <div class="img-block">
        <img src="<?php echo $image; ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
    </div>

    <div class="info-block">
        <div class="bonus-wrapper">
            <div class="bw-item">
                <span class="item-title"><?php echo __('Volatility', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo $volatility; ?></span>
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
    </div>

    <div class="details-block">
        <div class="dt-item">
            <i><?php echo __('Rows', 'foc-casino'); ?></i>
            <b><?php echo $rows; ?></b>
        </div>
        <div class="dt-item">
            <i><?php echo __('Min stake', 'foc-casino'); ?></i>
            <b><?php echo $min_bet; ?></b>
        </div>
        <div class="dt-item">
            <i><?php echo __('Paylines', 'foc-casino'); ?></i>
            <b><?php echo $paylines; ?></b>
        </div>
        <div class="dt-item">
            <i><?php echo __('Wheels', 'foc-casino'); ?></i>
            <b><?php echo $reels; ?></b>
        </div>
    </div>

    <?php if ($url): ?>
        <a href="<?php echo $url; ?>" class="cas-button"><?php echo __('Play', 'foc-casino'); ?></a>
    <?php endif; ?>
</div>