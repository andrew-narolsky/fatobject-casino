<?php

namespace FOC\Classes\Api;

/**
 * Base API class for all API service implementations.
 *
 * This class contains reusable logic for:
 * - building API URLs
 * - handling authentication
 * - sending HTTP requests
 * - parsing API responses
 *
 * Specific API modules (e.g., brands, casinos, games)
 * will extend this class and implement their own methods.
 */
class FocApi
{
    /**
     * Main API endpoint used by the service (e.g. "/casino-brands").
     *
     * Child classes should override this constant with a valid endpoint path.
     */
    protected const string ENDPOINT = '';

    /**
     * Endpoint used for retrieving select-style options
     * (usually "/entity/options").
     *
     * Child classes should override this constant if supported.
     */
    protected const string ENDPOINT_OPTIONS = '';

    /**
     * Base URL for the external API.
     */
    protected string $baseUrl;

    /**
     * API authentication token (optional).
     */
    protected string $apiToken;

    /**
     * Constructor.
     */
    public function __construct(string $baseUrl, string $apiToken)
    {
        $this->baseUrl = $baseUrl;
        $this->apiToken = $apiToken;
    }

    /**
     * Placeholder method for making API requests.
     *
     * Child classes may override or use shared logic here.
     */
    protected function request(string $endpoint, array $params = []): ?array
    {
        if (!$this->baseUrl) {
            return null;
        }

        $url = add_query_arg($params, $this->baseUrl . $endpoint);

        $headers = [];

        if ($this->apiToken) {
            $headers['X-Satellite-Key'] = $this->apiToken;
        }

        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return is_array($data) ? $data : null;
    }

    /**
     * Build and send an API request with supported query parameters.
     */
    protected function apiRequest(
        string $endpoint,
        array $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null,
        ?int $currentPage = null,
        ?int $perPage = null,
        ?int $limit = null
    ): ?array {
        $params = [];

        if (!empty($filters)) {
            $params['filters'] = $filters;
        }

        if ($sortBy) {
            $params['sort_by'] = $sortBy;
        }

        if ($sortOrder) {
            $params['sort_order'] = $sortOrder;
        }

        if ($currentPage !== null) {
            $params['page'] = $currentPage;
        }

        if ($perPage !== null) {
            $params['per_page'] = $perPage;
        }

        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        return $this->request($endpoint, $params);
    }

    /**
     * Get paginated data.
     */
    protected function getPaginatedData(
        int $currentPage = 1,
        int $perPage = 10,
        array $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null
    ): ?array {
        return $this->apiRequest(
            static::ENDPOINT,
            $filters,
            $sortBy,
            $sortOrder,
            $currentPage,
            $perPage
        );
    }

    /**
     * Get the option's list (id => name).
     */
    protected function getOptionsData(
        array $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null
    ): ?array {
        return $this->apiRequest(
            static::ENDPOINT_OPTIONS,
            $filters,
            $sortBy,
            $sortOrder
        );
    }

    /**
     * Get a single item by ID.
     */
    protected function getByIdData(int $id): ?array
    {
        return $this->apiRequest(static::ENDPOINT . '/' . $id);
    }
}
