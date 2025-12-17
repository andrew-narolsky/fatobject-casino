<?php

namespace FOC\Classes\Api\Abstracts;

/**
 * Abstracts API class for all API service implementations.
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
abstract class FocAbstractApi
{
    /**
     * Abstracts URL for the external API.
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
     * Must return the endpoint for the API service.
     */
    abstract protected static function endpoint(): string;

    /**
     * Endpoint used for retrieving select-style options
     * (usually "/entity/options").
     */
    protected static function getOptionsEndpoint(): string
    {
        return static::endpoint() . '/options';
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
        string  $endpoint,
        array   $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null,
        ?int    $currentPage = null,
        ?int    $perPage = null,
        ?int    $limit = null
    ): ?array
    {
        $params = [];
        if (!empty($filters)) $params['filters'] = $filters;
        if ($sortBy) $params['sort_by'] = $sortBy;
        if ($sortOrder) $params['sort_order'] = $sortOrder;
        if ($currentPage !== null) $params['page'] = $currentPage;
        if ($perPage !== null) $params['per_page'] = $perPage;
        if ($limit !== null) $params['limit'] = $limit;

        return $this->request($endpoint, $params);
    }

    /**
     * Get paginated data.
     */
    public function getPaginated(
        int     $currentPage = 1,
        int     $perPage = 10,
        array   $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null
    ): ?array
    {
        return $this->apiRequest(static::endpoint(), $filters, $sortBy, $sortOrder, $currentPage, $perPage);
    }

    /**
     * Get the option's list (id => name).
     */
    public function getOptions(
        array   $filters = [],
        ?string $sortBy = null,
        ?string $sortOrder = null
    ): ?array
    {
        return $this->apiRequest(static::getOptionsEndpoint(), $filters, $sortBy, $sortOrder);
    }

    /**
     * Get a single item by ID.
     */
    public function getById(int $id): ?array
    {
        return $this->apiRequest(static::endpoint() . '/' . $id);
    }
}
