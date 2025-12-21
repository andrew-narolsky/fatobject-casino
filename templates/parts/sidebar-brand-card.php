<?php
$data = foc_get_brand_card_data(get_the_ID());
?>

<div class="brand-card">
    <?php if ($data['rating']): ?>
        <div class="rate-block rate-<?php echo floor((float)$data['rating']); ?>">
            <span class="rate"><?php echo $data['rating']; ?>/<?php echo $data['max_rating']; ?></span>
        </div>
    <?php endif; ?>

    <div class="img-block" style="background: <?php echo esc_attr($data['background']); ?>">
        <img src="<?php echo esc_url($data['image']); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
    </div>

    <?php if ($data['bonus']): ?>
        <div class="info-block">
            <div class="info-block-top">
                <div class="title"><?php echo __('Welcome Bonus', 'foc-casino'); ?></div>
                <div class="bonus-wrapper">
                    <div class="bw-item">
                        <span class="item-title"><?php echo __('Bonus', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo esc_html($data['bonus']['money_amount'] ? $data['bonus']['currency'] . $data['bonus']['money_amount'] : '—'); ?></span>
                    </div>
                    <div class="bw-item">
                        <span class="item-title"><?php echo __('Free Spins', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo esc_html($data['bonus']['free_spins']); ?></span>
                    </div>
                    <div class="bw-item">
                        <span class="item-title"><?php echo __('Wagering', 'foc-casino'); ?></span>
                        <span class="item-value"><?php echo esc_html($data['bonus']['wager_requirements']); ?></span>
                    </div>
                </div>
            </div>
            <div class="promo-block">
                <span class="item-title"><?php echo __('Promo Code', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo esc_html($data['bonus']['bonus_code'] ?: '—'); ?></span>
                <?php if ($data['bonus']['bonus_code']): ?>
                    <button class="copy-clipboard"></button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($data['url']): ?>
        <a href="<?php echo esc_url($data['url']); ?>" class="cas-button"><?php echo __('Play', 'foc-casino'); ?></a>
    <?php endif; ?>
</div>
