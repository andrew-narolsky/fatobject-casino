<?php
$meta = get_post_meta(get_the_ID());

$image = $meta['image'][0] ?: esc_url(FOC_PLUGIN_URL . 'assets/img/brand-logo.svg');
$url = foc_normalize_url($meta['url'][0] ?? null);
$rating = $meta['rating'][0] ?: '5.00';
$msxRatting = 5;

$volatility = $meta['volatility'][0] ?: '—';
$max_profit = $meta['max_profit'][0] ?: '—';
$payout_percentage = $meta['payout_percentage'][0] ?: '—';

$rows = $meta['rows'][0] ?: '—';
$min_bet = $meta['min_bet'][0] ?: '—';
$paylines = $meta['paylines'][0] ?: '—';
$reels = $meta['reels'][0] ?: '—';
?>

<div class="slot-card">
    <div class="rate-block rate-<?php echo floor((float)$rating); ?>">
        <span class="rate"><?php echo $rating; ?>/<?php echo $msxRatting; ?></span>
    </div>

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
                <span class="item-value"><?php echo $payout_percentage . '%'; ?></span>
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