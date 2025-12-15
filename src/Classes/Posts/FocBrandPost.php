<?php

namespace FOC\Classes\Posts;

use FOC\Models\FocBrandModel;

/**
 * FocBrandPost
 *
 * Custom post-type class for Brands.
 *
 * Extends the abstract {@see FocPostType} class and defines
 * the specific slug, plural name, and singular name for the
 * "Brand" post-type.
 *
 * This class can be registered via {@see FocPostType::register()}
 * to make the Brands post-type available in WordPress.
 */
class FocBrandPost extends FocPostType
{
    /**
     * Post type slug.
     */
    protected static function slug(): string
    {
        return 'brand';
    }

    /**
     * Get the plural name of the post-type.
     */
    protected static function getName(): string
    {
        return 'Brands';
    }

    /**
     * Get the singular name of the post-type.
     */
    protected static function getSingularName(): string
    {
        return 'Brand';
    }

    /**
     * Get the menu icon URL or Dashicon class.
     */
    protected static function menuIcon(): ?string
    {
        return 'dashicons-sos';
    }

    /**
     * Register meta-boxes for the post-type
     */
    public static function registerMetaBoxes(): void
    {
        add_action('add_meta_boxes', function () {
            add_meta_box(
                'foc_brand_meta',
                'Brand Details',
                [self::class, 'renderMetaBox'],
                static::slug(),
                'normal',
                'default'
            );
        });

        // Save post-meta
        add_action('save_post_' . static::slug(), [self::class, 'saveMetaBox']);
    }

    /**
     * Render meta-box fields based on FocBrandModel fillable attributes
     */
    public static function renderMetaBox($post): void
    {
        // Nonce for security
        wp_nonce_field('foc_brand_meta_nonce', 'foc_brand_meta_nonce');

        foreach (FocBrandModel::getFillable() as $field) {
            if ($field === 'id') continue; // skip ID

            $value = get_post_meta($post->ID, $field, true);
            ?>
            <p>
                <label for="<?php echo esc_attr($field); ?>"><?php echo esc_html(ucfirst(str_replace('_', ' ', $field))); ?>:</label>
                <input type="text" name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($value); ?>" class="widefat">
            </p>
            <?php
        }
    }

    /**
     * Save meta-box values
     */
    public static function saveMetaBox($postId): void
    {
        if (!isset($_POST['foc_brand_meta_nonce']) || !wp_verify_nonce($_POST['foc_brand_meta_nonce'], 'foc_brand_meta_nonce')) {
            return;
        }

        foreach (FocBrandModel::getFillable() as $field) {
            if ($field === 'id') continue;

            if (isset($_POST[$field])) {
                update_post_meta($postId, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}