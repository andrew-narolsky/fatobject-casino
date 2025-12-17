<?php

namespace FOC\Classes\Posts\Abstracts;

/**
 * FocAbstractPostType
 *
 * Abstract base class for custom post types within the plugin.
 *
 * Provides a reusable template for registering post-types in WordPress,
 * including common arguments like labels, archive support, editor features,
 * and an optional menu icon.
 *
 * Concrete post-types (e.g., brands, slots, games) should extend this class
 * and define their own slug, name, singular name, and optionally a menu icon.
 *
 * This approach ensures a consistent and maintainable way to handle
 * multiple custom post types in the plugin.
 */
abstract class FocAbstractPostType
{
    /**
     * Post type slug.
     *
     * Must be defined by the child class.
     */
    abstract protected static function slug(): string;

    /**
     * Get the plural name of the post-type.
     *
     * Must be implemented by the child class.
     */
    abstract protected static function getName(): string;

    /**
     * Get the singular name of the post-type.
     *
     * Must be implemented by the child class.
     */
    abstract protected static function getSingularName(): string;

    /**
     * Model class that provides meta-fields
     *
     * Must be implemented by the child class.
     */
    abstract protected static function model(): string;

    /**
     * Register the post-type with WordPress.
     *
     * Builds labels and arguments automatically based on
     * the child class implementation and calls register_post_type().
     */
    public static function register(): void
    {
        $labels = [
                'name' => static::getName(),
                'singular_name' => static::getSingularName(),
        ];

        $args = [
                'labels' => $labels,
                'public' => true,
                'has_archive' => true,
                'supports' => ['title', 'editor'],
                'menu_icon' => static::menuIcon(),
        ];

        register_post_type(static::slug(), $args);
    }

    /**
     * Get the menu icon URL or Dashicon class.
     *
     * Can be overridden by the child class to provide a custom icon.
     */
    protected static function menuIcon(): ?string
    {
        return null;
    }

    /**
     * Register meta-boxes for the post-type
     */
    public static function registerMetaBoxes(): void
    {
        add_action('add_meta_boxes', function () {
            add_meta_box(
                    static::slug() . '_meta',
                    static::getSingularName() . ' Details',
                    [static::class, 'renderMetaBox'],
                    static::slug()
            );
        });

        add_action(
                'save_post_' . static::slug(),
                [static::class, 'saveMetaBox']
        );
    }

    /**
     * Render meta-box fields based on FocBrandModel fillable attributes
     */
    public static function renderMetaBox($post): void
    {
        /** @var class-string $model */
        $model = static::model();

        wp_nonce_field(
                static::slug() . '_meta_nonce',
                static::slug() . '_meta_nonce'
        );

        $repeaters = $model::getRepeaters();
        $repeaterKeys = array_keys($repeaters);
        $disabledFields = $model::getDisabledFields();

        // Render regular fields (exclude fields that are part of repeaters)
        foreach ($model::getFillable() as $field) {
            if (in_array($field, $repeaterKeys, true)) {
                continue; // skip, it's part of a repeater
            }

            $value = get_post_meta($post->ID, $field, true);
            $disabled = in_array($field, $disabledFields, true) ? 'disabled' : '';
            ?>
            <p>
                <label><?php echo esc_html(ucfirst(str_replace('_', ' ', $field))); ?></label>
                <input
                        type="text"
                        name="<?php echo esc_attr($field); ?>"
                        value="<?php echo esc_attr($value); ?>"
                        class="widefat"
                        <?php echo $disabled; ?>
                >
            </p>
            <?php
        }

        // Render repeaters
        foreach ($repeaters as $metaKey => $fields) {
            $values = get_post_meta($post->ID, $metaKey, true) ?: [];

            if (!empty($values) && !isset($values[0])) {
                $values = [$values];
            }

            echo '<hr>';
            echo '<strong>' . esc_html(ucfirst(str_replace('_', ' ', $metaKey))) . '</strong>';

            foreach ($values as $index => $row) {
                echo '<div style="border:1px solid #ddd;padding:10px;margin:10px 0;">';

                foreach ($fields as $field) {
                    $value = $row[$field] ?? '';
                    $disabled = in_array($metaKey, $disabledFields, true) ? 'disabled' : '';
                    ?>
                    <p>
                        <label><?php echo esc_html(ucfirst($field)); ?></label>
                        <input
                                type="text"
                                name="<?php echo esc_attr($metaKey); ?>[<?php echo $index; ?>][<?php echo esc_attr($field); ?>]"
                                value="<?php echo esc_attr($value); ?>"
                                class="widefat"
                                <?php echo $disabled; ?>
                        >
                    </p>
                    <?php
                }

                echo '</div>';
            }

            echo '<div><em>Existing items — edit and save if needed</em></div>';
        }
    }

    /**
     * Save meta-box values
     */
    public static function saveMetaBox(int $postId): void
    {
        /** @var class-string $model */
        $nonce = static::slug() . '_meta_nonce';

        if (!isset($_POST[$nonce]) || !wp_verify_nonce($_POST[$nonce], $nonce)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $model = static::model();

        // Save simple fillable fields
        foreach ($model::getFillable() as $field) {
            if (isset($_POST[$field])) {
                update_post_meta(
                        $postId,
                        $field,
                        sanitize_text_field($_POST[$field])
                );
            }
        }

        // Save repeater fields
        $repeaters = $model::getRepeaters();

        foreach ($repeaters as $metaKey => $fields) {
            if (!isset($_POST[$metaKey]) || !is_array($_POST[$metaKey])) {
                continue;
            }

            $sanitizedRows = [];
            foreach ($_POST[$metaKey] as $row) {
                $sanitizedRow = [];
                foreach ($fields as $field) {
                    $sanitizedRow[$field] = isset($row[$field]) ? sanitize_text_field($row[$field]) : '';
                }
                $sanitizedRows[] = $sanitizedRow;
            }

            update_post_meta($postId, $metaKey, $sanitizedRows);
        }
    }
}
