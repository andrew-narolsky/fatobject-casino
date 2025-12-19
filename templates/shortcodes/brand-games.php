<?php
$meta = get_post_meta(get_the_ID());

$games = foc_get_meta_value($meta, 'games', []);
if (is_string($games)) {
    $games = maybe_unserialize($games);
}
?>

<?php if ($games): ?>
    <div class="foc-casino__best-games">
        <div class="best-games-wrapper">
            <?php foreach ($games as $game): ?>
                <div class="item">
                    <div class="img-block">
                        <img src="<?php echo esc_url($game['image']); ?>" alt="<?php echo esc_attr($game['name']); ?>" loading="lazy">
                    </div>
                    <div class="game-name"><?php echo esc_attr($game['name']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="show-more-block">
            <button class="show-more"
                    data-toggle-wrapper=".best-games-wrapper"
                    data-toggle-root=".foc-casino__best-games"
            ><?php echo __('Show more', 'foc-casino'); ?></button>
        </div>
    </div>
<?php endif; ?>