<?php

namespace FOC\Interfaces;

/**
 * FocApiInterface
 *
 * Defines the contract for all API service classes.
 *
 * Implementing classes are responsible for communicating with
 * external APIs and providing normalized data structures that
 * can be consumed by the application.
 */
interface FocApiInterface
{
    /**
     * Get paginated data.
     */
    public function getPaginated(
        int $currentPage = 1,
        int $perPage = 10,
        array $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null
    ): ?array;

    /**
     * Get the option's list (id => name).
     */
    public function getOptions(
        array $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null
    ): ?array;

    /**
     * Get a single item by ID.
     */
    public function getById(int $id): ?array;
}
