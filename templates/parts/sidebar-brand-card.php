<?php
$meta = get_post_meta(get_the_ID());

$image = foc_get_meta_value($meta, 'image', esc_url(FOC_PLUGIN_URL . 'assets/img/brand-logo.svg'));
$url = foc_normalize_url(foc_get_meta_value($meta, 'url', null));
$background = foc_get_meta_value($meta, 'background', '#eee');
$rating = foc_get_meta_value($meta, 'rating', null);
$maxRating = 5;

$bonuses = foc_get_meta_value($meta, 'bonuses', []);
if (is_string($bonuses)) {
    $bonuses = maybe_unserialize($bonuses);
}

$firstDepositBonus = null;
foreach ($bonuses as $bonus) {
    if (($bonus['type'] ?? '') === 'first_deposit') {
        $firstDepositBonus = $bonus;
        break;
    }
}

$currency = '€';
$moneyAmount = $freeSpins = $wagerRequirements = '—';
$bonusCode = null;

if ($firstDepositBonus) {
    $currency = $firstDepositBonus['currency']['symbol'] ?? '€';

    $moneyAmount = foc_get_bonus_value($firstDepositBonus, 'moneyAmount', null);
    $freeSpins = foc_get_bonus_value($firstDepositBonus, 'freeSpins');
    $wagerRequirements = foc_get_bonus_value($firstDepositBonus, 'wagerRequirements', '—', '', 'x');
    $bonusCode = foc_get_bonus_value($firstDepositBonus, 'bonusCode', null);
}
?>

<div class="brand-card">
    <?php if ($rating): ?>
        <div class="rate-block rate-<?php echo floor((float)$rating); ?>">
            <span class="rate"><?php echo $rating; ?>/<?php echo $maxRating; ?></span>
        </div>
    <?php endif; ?>

    <div class="img-block" style="background: <?php echo esc_attr($background); ?>">
        <img src="<?php echo esc_url($image); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
    </div>

    <?php if ($firstDepositBonus): ?>
        <div class="info-block">
            <div class="info-block-top">
                <div class="title"><?php echo __('Welcome Bonus', 'foc-casino'); ?></div>
                <div class="bonus-wrapper">
                    <div class="bw-item">
                        <span class="item-title"><?php echo __('Bonus', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo esc_html($moneyAmount ? $currency . $moneyAmount : '—'); ?></span>
                    </div>
                    <div class="bw-item">
                        <span class="item-title"><?php echo __('Free Spins', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo esc_html($freeSpins); ?></span>
                    </div>
                    <div class="bw-item">
                        <span class="item-title"><?php echo __('Wagering', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo esc_html($wagerRequirements); ?></span>
                    </div>
                </div>
            </div>
            <div class="promo-block">
                <span class="item-title"><?php echo __('Promo Code', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo esc_html($bonusCode ?: '—'); ?></span>
                <?php if ($bonusCode): ?>
                    <button class="copy-clipboard"></button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($url): ?>
        <a href="<?php echo esc_url($url); ?>" class="cas-button"><?php echo __('Play', 'foc-casino'); ?></a>
    <?php endif; ?>
</div>
