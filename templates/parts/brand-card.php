<?php
/**
 * @var int $page
 * @var int $per_page
 * @var int $index
 */
$number = (($page - 1) * $per_page) + $index + 1;
$permalink = get_the_permalink();
$data = foc_get_brand_card_data(get_the_ID());
?>

<div class="item">
    <div class="number"><?php echo $number; ?></div>

    <?php if ($data['rating']): ?>
        <div class="rate-block rate-<?php echo floor((float)$data['rating']); ?>">
            <span class="rate"><?php echo $data['rating']; ?>/<?php echo $data['max_rating']; ?></span>
        </div>
    <?php endif; ?>

    <a href="<?php echo $permalink; ?>" class="img-block"
       style="background: <?php echo esc_attr($data['background']); ?>">
        <img src="<?php echo esc_url($data['image']); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
    </a>

    <div class="info-block">
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
    <div class="button-wrapper">
        <?php if ($data['url']): ?>
            <a href="<?php echo esc_url($data['url']); ?>"
               class="cas-button"><?php echo __('Play', 'foc-casino'); ?></a>
        <?php endif; ?>
        <a href="<?php echo $permalink; ?>" class="cas-button white"><?php echo __('Review', 'foc-casino'); ?></a>
    </div>
</div>