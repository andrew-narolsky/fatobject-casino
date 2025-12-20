<?php

use FOC\Classes\Template\FocTemplateLoader;

if (!function_exists('foc_normalize_url')) {
    /**
     * Normalize a URL for safe output:
     * - Adds https:// if protocol is missing
     * - Escapes the URL
     * - Returns false if empty
     */
    function foc_normalize_url(?string $url): string|false
    {
        if (!$url) {
            return false;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        return esc_url($url);
    }
}

if (!function_exists('foc_get_meta_value')) {
    /**
     * Safe helper to get post-meta value by default.
     */
    function foc_get_meta_value(array $meta, string $key, mixed $default = '—'): mixed
    {
        return !empty($meta[$key][0]) ? $meta[$key][0] : $default;
    }
}

if (!function_exists('foc_get_bonus_value')) {
    /**
     * Safe helper to get a value from a bonus array.
     */
    function foc_get_bonus_value(
        ?array  $bonus,
        string  $key,
        ?string $default = '—',
        string  $prefix = '',
        string  $suffix = ''
    ): ?string
    {
        if (!$bonus || !array_key_exists($key, $bonus)) {
            return $default;
        }

        $value = $bonus[$key];

        if (is_array($value)) {
            $value = implode(', ', array_filter(
                $value,
                static fn($v) => $v !== null && $v !== ''
            ));
        }

        if ($value === null || $value === '') {
            return $default;
        }

        return $prefix . $value . $suffix;
    }
}

if (!function_exists('foc_snake_to_title')) {
    /**
     * Convert snake_case string to human-readable title.
     */
    function foc_snake_to_title(string $value): string
    {
        return ucwords(str_replace('_', ' ', $value));
    }
}

if (!function_exists('foc_get_bonus_type_class')) {
    /**
     * Get CSS class for a bonus type.
     */
    function foc_get_bonus_type_class(?string $type): string
    {
        if (!$type) {
            return '';
        }

        $map = [
            // Deposit bonuses
            'no_deposit'       => 'purple',
            'first_deposit'    => 'purple',
            'second_deposit'   => 'purple',
            'third_deposit'    => 'purple',
            'fourth_deposit'   => 'purple',
            'fifth_deposit'    => 'purple',
            'sixth_deposit'    => 'purple',
            'seventh_deposit'  => 'purple',
            'eighth_deposit'   => 'purple',
            'ninth_deposit'    => 'purple',

            // Reload / recurring
            'reload'           => 'blue',
            'cashback'         => 'blue',
            'daily'            => 'blue',
            'referral'         => 'blue',

            // Special bonuses
            'high_roller'      => 'red',
            'sticky_bonus'     => 'red',
            'none_sticky'      => 'red',
            'exclusive'        => 'red',
            'crypto'           => 'red',
        ];

        return $map[$type] ?? '';
    }
}

if (!function_exists('foc_dump')) {
    /**
     * Dump an mixed in a readable format.
     */
    function foc_dump(mixed $value): void
    {
        echo '<pre>';
        print_r($value);
        echo '</pre>';
    }
}

if (!function_exists('foc_render')) {
    /**
     * Render a plugin template using FocTemplateLoader.
     *
     * This helper provides a simple, procedural wrapper around
     * FocTemplateLoader::render() to avoid repetitive static calls
     * in templates and improve readability.
     *
     * Templates can be overridden by the active theme.
     */
    function foc_render(string $template, array $context = []): void
    {
        FocTemplateLoader::render($template, $context);
    }
}

if (!function_exists('foc_build_query_args')) {
    /**
     * Build WP_Query arguments for post-list shortcodes and Load More requests.
     *
     * This helper normalizes and prepares query arguments based on a unified
     * configuration array. It supports:
     * - pagination
     * - filtering by specific post-IDs (with preserved order)
     * - standard ordering
     * - meta field–based ordering
     *
     * Priority rules:
     * - If `ids` are provided, ordering is forced to `post__in`
     * - Otherwise, `orderby` / `order` are applied if present
     * - `meta_key` is applied only for meta-based sorting
     */
    function foc_build_query_args(array $config): array
    {
        $ids = is_array($config['ids'] ?? null) ? $config['ids'] : [];

        $args = [
            'post_type'      => $config['post_type'],
            'posts_per_page' => (int) $config['per_page'],
            'paged'          => (int) ($config['page'] ?? 1),
        ];

        if ($ids) {
            $args['post__in'] = $ids;
            $args['orderby'] = 'post__in';
        } elseif (!empty($config['orderby'])) {
            $args['orderby'] = $config['orderby'];
            $args['order']   = $config['order'] ?? 'DESC';
        }

        if (
            in_array($config['orderby'] ?? '', ['meta_value', 'meta_value_num'], true)
            && !empty($config['meta_key'])
        ) {
            $args['meta_key'] = $config['meta_key'];
        }

        return $args;
    }
}