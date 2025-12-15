<?php

namespace FOC\Traits;

/**
 * Proxy trait that exposes public API methods
 * and delegates them to the base FocApi class.
 *
 * This trait ensures that all API service classes have a consistent
 * public interface without duplicating method bodies.
 */
trait FocApiProxyTrait
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
    ): ?array {
        return $this->getPaginatedData($currentPage, $perPage, $filters, $sortBy, $sortOrder);
    }

    /**
     * Get the option's list (id => name).
     */
    public function getOptions(
        array $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null
    ): ?array {
        return $this->getOptionsData($filters, $sortBy, $sortOrder);
    }

    /**
     * Get a single item by ID.
     */
    public function getById(int $id): ?array
    {
        return $this->getByIdData($id);
    }
}