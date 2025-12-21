<?php
$data = foc_get_slot_card_data(get_the_ID());
$permalink = get_the_permalink();
?>

<div class="item">
    <a href="<?php echo $permalink; ?>" class="img-block">
        <img src="<?php echo esc_url($data['image']); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
    </a>

    <div class="info-block">
        <a href="<?php echo $permalink; ?>" class="item-title"><?php echo get_the_title(); ?></a>

        <?php if ($data['rating']): ?>
            <div class="rate-block rate-<?php echo floor((float)$data['rating']); ?>">
                <span class="rate"><?php echo $data['rating']; ?>/<?php echo $data['max_rating']; ?></span>
            </div>
        <?php endif; ?>

        <div class="bonus-wrapper">
            <div class="bw-item">
                <span class="item-title"><?php echo __('Volatility', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo foc_snake_to_title($data['volatility']); ?></span>
            </div>
            <div class="bw-item">
                <span class="item-title"><?php echo __('Max Profit', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo esc_html($data['max_profit']); ?></span>
            </div>
            <div class="bw-item">
                <span class="item-title"><?php echo __('RTP', 'foc-casino'); ?></span>
                <span class="item-value"><?php echo esc_html($data['payout_percentage']); ?></span>
            </div>
        </div>
        <div class="button-wrapper">
            <?php if ($data['url']): ?>
                <a href="<?php echo esc_url($data['url']); ?>" class="cas-button"><?php echo __('Play', 'foc-casino'); ?></a>
            <?php endif; ?>
            <a href="<?php echo $permalink; ?>" class="cas-button white"><?php echo __('Review', 'foc-casino'); ?></a>
        </div>

        <?php if ($data['software_provider']): ?>
            <div class="game-provider"><?php echo __('Game provider: ', 'foc-casino'); ?>
                <b><?php echo $data['software_provider']; ?></b>
            </div>
        <?php endif; ?>
    </div>
</div>