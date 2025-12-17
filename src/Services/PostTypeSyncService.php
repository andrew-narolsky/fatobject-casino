<?php

namespace FOC\Services;

/**
 * PostTypeSyncService
 *
 * Service responsible for synchronizing and managing WordPress custom post types
 * with external API data.
 *
 * Features:
 * - Sync API entities to WP posts, optionally including meta-fields
 * - Update existing posts based on external IDs
 * - Set missing posts to draft if they are no longer present in the API
 * - Create new posts if they do not exist
 * - Perform bulk deletion of posts and their associated post-meta
 *
 * Intended for use in background processes, import jobs, or other scenarios
 * where WordPress content needs to be kept in sync with an external API efficiently.
 */
class PostTypeSyncService
{
    /**
     * Sync API entities with a WordPress custom post type.
     */
    public function syncPostTypeFromApi(
        array  $items,
        string $postType,
        string $metaKey = 'external_id',
        string $titleKey = 'name',
        string $idKey = 'id'
    ): void
    {
        $existingMap = $this->getExistingPostsMap($postType, $metaKey);

        $apiIds = array_map(
            static fn($item) => (int)($item[$idKey] ?? 0),
            $items
        );

        foreach ($items as $item) {
            if (empty($item[$idKey]) || empty($item[$titleKey])) {
                continue;
            }

            $this->createOrUpdatePost(
                $item[$titleKey],
                (int)$item[$idKey],
                $existingMap,
                $postType,
                $metaKey
            );
        }

        $this->draftMissingPosts($existingMap, $apiIds);
    }

    /**
     * Sync API entities including meta-fields.
     */
    public function syncPostTypeFromApiWithMeta(
        ?array $items,
        string $postType,
        string $metaKey = 'external_id',
        string $titleKey = 'name',
        string $idKey = 'id',
        array  $fillable = [],
        array  $keyMap = []
    ): void
    {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (empty($item[$idKey]) || empty($item[$titleKey])) {
                continue;
            }

            $externalId = (int)$item[$idKey];
            $title = (string)$item[$titleKey];

            $posts = get_posts([
                'post_type' => $postType,
                'meta_key' => $metaKey,
                'meta_value' => $externalId,
                'posts_per_page' => 1,
                'fields' => 'ids',
            ]);

            if (empty($posts)) {
                continue;
            }

            $postId = $posts[0];

            wp_update_post([
                'ID' => $postId,
                'post_title' => $title,
                'post_status' => 'publish',
            ]);

            foreach ($fillable as $field) {
                if ($field === $metaKey) continue;

                $value = self::resolveMetaValue($field, $item, $keyMap);

                if ($value !== null) {
                    update_post_meta($postId, $field, $value);
                }
            }
        }
    }

    /**
     * Resolve a WordPress meta-field value from an API item.
     *
     * This method supports three resolution strategies:
     *
     * 1) Explicit key mapping (simple)
     *    Example:
     *    [
     *        'payoutPercentage' => 'payout_percentage'
     *    ]
     *
     * 2) Explicit key mapping (complex / nested)
     *    Example:
     *    [
     *        'softwareProvider' => [
     *            'meta' => 'software_provider',
     *            'value' => 'name',
     *        ]
     *    ]
     *
     * 3) Fallback mapping
     *    Converts snake_case meta-key to camelCase API key:
     *    software_provider → softwareProvider
     *
     * The first matching strategy wins.
     */
    private static function resolveMetaValue(
        string $metaKey,
        array  $item,
        array  $keyMap
    ): mixed
    {
        // 1. direct mapping exists
        foreach ($keyMap as $apiKey => $map) {

            // simple string mapping
            if ($map === $metaKey && isset($item[$apiKey])) {
                return $item[$apiKey];
            }

            // complex mapping
            if (is_array($map) && ($map['meta'] ?? null) === $metaKey) {
                return $item[$apiKey] ?? null;
            }
        }

        // 2. fallback: snake_case → camelCase
        $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $metaKey))));

        return $item[$camel] ?? null;
    }

    /**
     * Return a map of external ID => post ID.
     */
    private function getExistingPostsMap(string $postType, string $metaKey): array
    {
        $existingPosts = get_posts([
            'post_type' => $postType,
            'numberposts' => -1,
            'post_status' => ['publish', 'draft'],
            'meta_key' => $metaKey,
        ]);

        $map = [];
        foreach ($existingPosts as $post) {
            $externalId = (int)get_post_meta($post->ID, $metaKey, true);
            if ($externalId) {
                $map[$externalId] = $post->ID;
            }
        }

        return $map;
    }

    /**
     * Create a new post or update the existing one by external ID.
     */
    private function createOrUpdatePost(
        string $title,
        int    $externalId,
        array  &$existingMap,
        string $postType,
        string $metaKey
    ): void
    {
        if (isset($existingMap[$externalId])) {
            $postId = $existingMap[$externalId];
            wp_update_post([
                'ID' => $postId,
                'post_title' => $title,
                'post_status' => 'publish',
            ]);
        } else {
            $postId = wp_insert_post([
                'post_type' => $postType,
                'post_title' => $title,
                'post_status' => 'publish',
            ]);

            if (!is_wp_error($postId)) {
                update_post_meta($postId, $metaKey, $externalId);
                $existingMap[$externalId] = $postId;
            }
        }

    }

    /**
     * Set posts to draft if they are missing from the API.
     */
    private function draftMissingPosts(array $existingMap, array $apiIds): void
    {
        foreach ($existingMap as $externalId => $postId) {
            if (!in_array($externalId, $apiIds, true)) {
                wp_update_post([
                    'ID' => $postId,
                    'post_status' => 'draft',
                ]);
            }
        }
    }

    /**
     * Delete all posts (and their post meta) for the given post types.
     *
     * This method performs a bulk delete using SQL for maximum performance.
     * It removes:
     *  - records from wp_posts
     *  - related records from wp_postmeta
     *
     * Intended to be used in background processes where large amounts
     * of content must be removed efficiently.
     */
    public static function deleteByPostTypes(array $postTypes): void
    {
        global $wpdb;

        if (empty($postTypes)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($postTypes), '%s'));

        $postIds = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ($placeholders)",
                ...$postTypes
            )
        );

        if (!$postIds) {
            return;
        }

        $ids = implode(',', array_map('intval', $postIds));

        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($ids)");
        $wpdb->query("DELETE FROM {$wpdb->posts} WHERE ID IN ($ids)");
    }
}