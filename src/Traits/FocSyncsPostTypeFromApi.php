<?php

namespace FOC\Traits;

/**
 * FocSyncsPostTypeFromApi
 *
 * Reusable logic for synchronizing API entities
 * with WordPress custom post types.
 *
 * - Creates new posts if they do not exist
 * - Updates existing posts by external ID
 * - Sets posts to draft if they no longer exist in API
 *
 * Intended to be used by background processes and import jobs.
 */
trait FocSyncsPostTypeFromApi
{
    /**
     * Sync API entities with a WordPress custom post type.
     */
    protected function syncPostTypeFromApi(
        array $items,
        string $postType,
        string $metaKey = 'external_id',
        string $titleKey = 'name',
        string $idKey = 'id'
    ): void {
        $existingMap = $this->getExistingPostsMap($postType, $metaKey);

        $apiIds = array_map(
            static fn($item) => (int) ($item[$idKey] ?? 0),
            $items
        );

        foreach ($items as $item) {
            if (empty($item[$idKey]) || empty($item[$titleKey])) {
                continue;
            }

            $this->createOrUpdatePost(
                $item[$titleKey],
                (int) $item[$idKey],
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
    protected function syncPostTypeFromApiWithMeta(
        array $items,
        string $postType,
        string $metaKey = 'external_id',
        string $titleKey = 'name',
        string $idKey = 'id',
        array $fillable = [],
        array $keyMap = []
    ): void {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (empty($item[$idKey]) || empty($item[$titleKey])) {
                continue;
            }

            $externalId = (int) $item[$idKey];
            $title      = (string) $item[$titleKey];

            $posts = get_posts([
                'post_type'      => $postType,
                'meta_key'       => $metaKey,
                'meta_value'     => $externalId,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]);

            if (empty($posts)) {
                continue;
            }

            $postId = $posts[0];

            wp_update_post([
                'ID'          => $postId,
                'post_title'  => $title,
                'post_status' => 'publish',
            ]);

            foreach ($fillable as $field) {
                if ($field === $metaKey) continue;

                $apiField = array_search($field, $keyMap, true) ?: $field;

                if (isset($item[$apiField])) {
                    update_post_meta($postId, $field, $item[$apiField]);
                }
            }
        }
    }

    /**
     * Return a map of external ID => post ID.
     */
    private function getExistingPostsMap(string $postType, string $metaKey): array
    {
        $existingPosts = get_posts([
            'post_type'   => $postType,
            'numberposts' => -1,
            'post_status' => ['publish', 'draft'],
            'meta_key'    => $metaKey,
        ]);

        $map = [];
        foreach ($existingPosts as $post) {
            $externalId = (int) get_post_meta($post->ID, $metaKey, true);
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
        int $externalId,
        array &$existingMap,
        string $postType,
        string $metaKey
    ): int {
        if (isset($existingMap[$externalId])) {
            $postId = $existingMap[$externalId];
            wp_update_post([
                'ID'          => $postId,
                'post_title'  => $title,
                'post_status' => 'publish',
            ]);
        } else {
            $postId = wp_insert_post([
                'post_type'   => $postType,
                'post_title'  => $title,
                'post_status' => 'publish',
            ]);

            if (!is_wp_error($postId)) {
                update_post_meta($postId, $metaKey, $externalId);
                $existingMap[$externalId] = $postId;
            }
        }

        return $postId;
    }

    /**
     * Set posts to draft if they are missing from the API.
     */
    private function draftMissingPosts(array $existingMap, array $apiIds): void
    {
        foreach ($existingMap as $externalId => $postId) {
            if (!in_array($externalId, $apiIds, true)) {
                wp_update_post([
                    'ID'          => $postId,
                    'post_status' => 'draft',
                ]);
            }
        }
    }
}