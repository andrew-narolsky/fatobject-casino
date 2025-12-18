<?php
$meta = get_post_meta(get_the_ID());

$image = $meta['image'][0] ?: esc_url(FOC_PLUGIN_URL . 'assets/img/brand-logo.svg');
$url = foc_normalize_url($meta['url'][0] ?? null);
$background = $meta['background'][0] ?: '#eee;';
$rating = $meta['rating'][0] ?: '5.00';
$msxRatting = 5;

$bonuses = !empty($meta['bonuses'][0]) ? maybe_unserialize($meta['bonuses'][0]) : [];
$firstDepositBonus = null;
foreach ($bonuses as $bonus) {
    if (($bonus['type'] ?? '') === 'first_deposit') {
        $firstDepositBonus = $bonus;
        break;
    }
}
$currency = $firstDepositBonus['currency'] ?: '€';
?>

<div class="brand-card">
    <div class="rate-block rate-<?php echo floor((float)$rating); ?>">
        <span class="rate"><?php echo $rating; ?>/<?php echo $msxRatting; ?></span>
    </div>

    <div class="img-block" style="background: <?php echo $background; ?>">
        <img src="<?php echo $image; ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
    </div>

    <?php if ($firstDepositBonus): ?>
        <div class="info-block">
            <div class="info-block-top">
                <div class="title"><?php echo __('Welcome Bonus', 'foc-casino'); ?></div>
                <div class="bonus-wrapper">
                    <div class="bw-item">
                        <span class="item-title"><?php echo __('Bonus', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo $currency . $firstDepositBonus['moneyAmount']; ?></span>
                    </div>
                    <div class="bw-item">
                        <span class="item-title"><?php echo __('Free Spins', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo $firstDepositBonus['freeSpins']; ?></span>
                    </div>
                    <div class="bw-item">
                        <span class="item-title"><?php echo __('Wagering', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo $firstDepositBonus['wagerRequirements'] . 'x'; ?></span>
                    </div>
                </div>
            </div>
            <div class="promo-block">
                <span class="item-title"><?php echo __('Promo Code', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo $firstDepositBonus['bonusCode'] ?: '—'; ?></span>
                <button class="copy-clipboard"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($url): ?>
        <a href="<?php echo $url; ?>" class="cas-button"><?php echo __('Play', 'foc-casino'); ?></a>
    <?php endif; ?>
</div>