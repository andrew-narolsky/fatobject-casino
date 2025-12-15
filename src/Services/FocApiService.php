<?php

namespace FOC\Services;

use FOC\Interfaces\FocApiInterface;

/**
 * API Service class using the Strategy pattern.
 * Allows dynamic switching between different API data sources.
 */
class FocApiService
{
    /**
     * Current API strategy instance.
     */
    protected ?FocApiInterface $strategy = null;

    /**
     * Set the API strategy dynamically.
     */
    public function setStrategy(FocApiInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

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
        if ($this->strategy === null) {
            return [];
        }

        return $this->strategy->getPaginated(
            $currentPage,
            $perPage,
            $filters,
            $sortBy,
            $sortOrder
        );
    }

    /**
     * Get an option's list (id => name).
     */
    public function getOptions(
        array $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null
    ): ?array {
        if ($this->strategy === null) {
            return [];
        }

        return $this->strategy->getOptions(
            $filters,
            $sortBy,
            $sortOrder
        );
    }

    /**
     * Get a single item by ID.
     */
    public function getById(int $id): ?array
    {
        return $this->strategy?->getById($id);
    }
}
