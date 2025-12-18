<?php

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

        // Add https:// if missing
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        return esc_url($url);
    }
}