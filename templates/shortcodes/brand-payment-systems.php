<?php
$meta = get_post_meta(get_the_ID());

$paymentSystems = foc_get_meta_value($meta, 'payment_systems', []);
if (is_string($paymentSystems)) {
    $paymentSystems = maybe_unserialize($paymentSystems);
}
?>

<?php if ($paymentSystems): ?>
    <div class="foc-casino__best-payments">
        <div class="best-payments-wrapper">
            <?php foreach ($paymentSystems as $paymentSystem): ?>
                <div class="item">
                    <img src="<?php echo esc_url($paymentSystem['image']); ?>"
                         alt="<?php echo esc_attr($paymentSystem['name']); ?>" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="show-more-block">
            <button class="show-more"
                    data-toggle-wrapper=".best-payments-wrapper"
                    data-toggle-root=".foc-casino__best-payments"
            ><?php echo __('Show more', 'foc-casino'); ?></button>
        </div>
    </div>
<?php endif; ?>