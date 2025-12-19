<?php
$meta = get_post_meta(get_the_ID());

$bonuses = foc_get_meta_value($meta, 'bonuses', []);
if (is_string($bonuses)) {
    $bonuses = maybe_unserialize($bonuses);
}
?>

<?php if ($bonuses): ?>
    <div class="foc-casino__best-bonuses">
        <?php foreach ($bonuses as $bonus): ?>
            <?php
            $type = foc_get_bonus_value($bonus, 'type');

            $currency = $bonus['currency']['symbol'] ?? '€';

            $moneyAmount = foc_get_bonus_value($bonus, 'moneyAmount', null);
            $bonusCode = foc_get_bonus_value($bonus, 'bonusCode', null);
            $wagerRequirements = foc_get_bonus_value($bonus, 'wagerRequirements', '—', '', 'x');

            $maxBet = foc_get_bonus_value($bonus, 'maxBet', null);
            $maximumAmount = foc_get_bonus_value($bonus, 'maximumAmount', null);
            $minimumDeposit = foc_get_bonus_value($bonus, 'minimumDeposit', null);

            $forfeitable = foc_get_bonus_value($bonus, 'forfeitable', null);
            $stickyBonus = foc_get_bonus_value($bonus, 'stickyBonus', null);
            ?>
            <div class="item">
                <div class="info-block <?php echo esc_attr(foc_get_bonus_type_class($type)); ?>">
                    <div class="title"><?php echo foc_snake_to_title($type); ?></div>
                    <div class="bonus-wrapper">
                        <div class="bw-item">
                            <span class="item-title"><?php echo __('Bonus', 'foc-casino'); ?></span>
                            <span class="item-value"><?php echo esc_html($moneyAmount ? $currency . $moneyAmount : '—'); ?></span>
                        </div>
                        <div class="bw-item">
                            <span class="item-title"><?php echo __('Free Spins', 'foc-casino'); ?></span>
                            <span class="item-value"><?php echo esc_html(foc_get_bonus_value($bonus, 'freeSpins')); ?></span>
                        </div>
                        <div class="bw-item">
                            <span class="item-title"><?php echo __('Wagering', 'foc-casino'); ?></span>
                            <span class="item-value"><?php echo esc_html($wagerRequirements); ?></span>
                        </div>
                    </div>
                </div>
                <div class="about-bonus-item">
                    <div class="promo-block">
                        <span class="item-title"><?php echo __('Promo Code', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo esc_html($bonusCode ?: '—'); ?></span>
                        <?php if ($bonusCode): ?>
                            <button class="copy-clipboard"></button>
                        <?php endif; ?>
                    </div>
                    <div class="hide-block">
                        <div class="hide-block__inner">
                            <div class="about-bonus-row">
                                <span class="bon-title"><?php echo __('Bonus percentage', 'foc-casino'); ?></span>
                                <span class="bon-val"><?php echo esc_html(foc_get_bonus_value($bonus, 'moneyPercent', '—', '', '%')); ?></span>
                            </div>
                            <div class="about-bonus-row">
                                <span class="bon-title"><?php echo __('Wager requirements', 'foc-casino'); ?></span>
                                <span class="bon-val"><?php echo esc_html($wagerRequirements); ?></span>
                            </div>
                            <div class="about-bonus-row">
                                <span class="bon-title"><?php echo __('Expiration time', 'foc-casino'); ?></span>
                                <span class="bon-val"><?php echo esc_html(foc_get_bonus_value($bonus, 'daysActive')); ?></span>
                            </div>
                            <div class="about-bonus-row">
                                <span class="bon-title"><?php echo __('Max bet', 'foc-casino'); ?></span>
                                <span class="bon-val"><?php echo esc_html($maxBet ? $currency . $maxBet : '—'); ?></span>
                            </div>
                            <div class="about-bonus-row">
                                <span class="bon-title"><?php echo __('Max withdrawal', 'foc-casino'); ?></span>
                                <span class="bon-val"><?php echo esc_html($maximumAmount ? $currency . $maximumAmount : '—'); ?></span>
                            </div>
                            <div class="about-bonus-row">
                                <span class="bon-title"><?php echo __('Min deposit', 'foc-casino'); ?></span>
                                <span class="bon-val"><?php echo esc_html($minimumDeposit ? $currency . $minimumDeposit : '—'); ?></span>
                            </div>
                            <div class="about-bonus-row">
                                <span class="bon-title"><?php echo __('Forfeitable', 'foc-casino'); ?></span>
                                <span class="bon-val"><?php echo esc_html($forfeitable ? 'Yes' : 'No'); ?></span>
                            </div>
                            <div class="about-bonus-row">
                                <span class="bon-title"><?php echo __('Non sticky bonus', 'foc-casino'); ?></span>
                                <span class="bon-val"><?php echo esc_html($stickyBonus ? 'Yes' : 'No'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="show-more-block">
                        <button class="show-more"
                                data-toggle-item=".item"
                                data-toggle-wrapper=".hide-block"
                        ><?php echo __('Show more', 'foc-casino'); ?></button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif;