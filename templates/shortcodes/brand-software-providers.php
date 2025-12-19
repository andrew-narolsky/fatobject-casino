<?php
$meta = get_post_meta(get_the_ID());

$softwareProviders = foc_get_meta_value($meta, 'software_providers', []);
if (is_string($softwareProviders)) {
    $softwareProviders = maybe_unserialize($softwareProviders);
}
?>

<?php if ($softwareProviders): ?>
    <div class="foc-casino__best-providers">
        <div class="best-providers-wrapper">
            <?php foreach ($softwareProviders as $softwareProvider): ?>
                <div class="item">
                    <img src="<?php echo esc_url($softwareProvider['image']); ?>"
                         alt="<?php echo esc_attr($softwareProvider['name']); ?>" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="show-more-block">
            <button class="show-more"
                    data-toggle-wrapper=".best-providers-wrapper"
                    data-toggle-root=".foc-casino__best-providers"
            ><?php echo __('Show more', 'foc-casino'); ?></button>
        </div>
    </div>
<?php endif; ?>