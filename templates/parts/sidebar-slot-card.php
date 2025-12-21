<?php
$data = foc_get_slot_card_data(get_the_ID());
?>

<div class="slot-card">
    <?php if ($data['rating']): ?>
        <div class="rate-block rate-<?php echo floor((float)$data['rating']); ?>">
            <span class="rate"><?php echo $data['rating']; ?>/<?php echo $data['max_rating']; ?></span>
        </div>
    <?php endif; ?>

    <?php if ($data['software_provider']): ?>
        <div class="game-provider"><?php echo __('Game provider: ', 'foc-casino'); ?>
            <b><?php echo $data['software_provider']; ?></b>
        </div>
    <?php endif; ?>

    <div class="img-block">
        <img src="<?php echo esc_url($data['image']); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
    </div>

    <div class="info-block">
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
    </div>

    <div class="details-block">
        <div class="dt-item">
            <i><?php echo __('Rows', 'foc-casino'); ?></i>
            <b><?php echo esc_html($data['rows']); ?></b>
        </div>
        <div class="dt-item">
            <i><?php echo __('Min stake', 'foc-casino'); ?></i>
            <b><?php echo esc_html($data['min_bet']); ?></b>
        </div>
        <div class="dt-item">
            <i><?php echo __('Paylines', 'foc-casino'); ?></i>
            <b><?php echo esc_html($data['paylines']); ?></b>
        </div>
        <div class="dt-item">
            <i><?php echo __('Wheels', 'foc-casino'); ?></i>
            <b><?php echo esc_html($data['reels']); ?></b>
        </div>
    </div>

    <?php if ($data['url']): ?>
        <a href="<?php echo esc_url($data['url']); ?>" class="cas-button"><?php echo __('Play', 'foc-casino'); ?></a>
    <?php endif; ?>
</div>