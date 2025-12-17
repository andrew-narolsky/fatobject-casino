<?php

namespace FOC\Background\Abstracts;

use WP_Error;

/**
 * Class FocAbstractAsyncRequest
 *
 * Abstracts class for asynchronous AJAX requests.
 * Part of WP Background Processing.
 */
abstract class FocAbstractAsyncRequest
{
    /**
     * Prefix
     */
    protected string $prefix = 'wp';

    /**
     * Action
     */
    protected string $action = 'async_request';

    /**
     * Identifier
     */
    protected mixed $identifier;

    /**
     * Data
     */
    protected array $data = array();

    /**
     * Initiate new async request.
     */
    public function __construct()
    {
        $this->identifier = $this->prefix . '_' . $this->action;

        add_action('wp_ajax_' . $this->identifier, array($this, 'maybe_handle'));
        add_action('wp_ajax_nopriv_' . $this->identifier, array($this, 'maybe_handle'));
    }

    /**
     * Set data used during the request.
     */
    public function data($data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Dispatch the async request.
     */
    public function dispatch(): false|WP_Error|array
    {
        $url = add_query_arg($this->get_query_args(), $this->get_query_url());
        $args = $this->get_post_args();

        return wp_remote_post(esc_url_raw($url), $args);
    }

    /**
     * Get query args.
     */
    protected function get_query_args(): array
    {
        if (property_exists($this, 'query_args')) {
            return $this->query_args;
        }

        $args = [
            'action' => $this->identifier,
            'nonce' => wp_create_nonce($this->identifier),
        ];

        return apply_filters($this->identifier . '_query_args', $args);
    }

    /**
     * Get query URL.
     */
    protected function get_query_url(): string
    {
        if (property_exists($this, 'query_url')) {
            return $this->query_url;
        }

        $url = admin_url('admin-ajax.php');

        return apply_filters($this->identifier . '_query_url', $url);
    }

    /**
     * Get post args.
     */
    protected function get_post_args(): array
    {
        if (property_exists($this, 'post_args')) {
            return $this->post_args;
        }

        $args = [
            'timeout' => 5,
            'blocking' => false,
            'body' => $this->data,
            'cookies' => $_COOKIE,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        ];

        return apply_filters($this->identifier . '_post_args', $args);
    }

    /**
     * Maybe handle a dispatched request.
     *
     * Check for the correct nonce and pass to the handler.
     */
    public function maybe_handle(): mixed
    {
        session_write_close();

        check_ajax_referer($this->identifier, 'nonce');

        $this->handle();

        return $this->maybe_wp_die();
    }

    /**
     * Should the process exit with wp_die?
     */
    protected function maybe_wp_die(mixed $return = null): mixed
    {
        if (apply_filters($this->identifier . '_wp_die', true)) {
            wp_die();
        }

        return $return;
    }

    /**
     * Handle a dispatched request.
     *
     * Override this method to perform any actions required
     * during the async request.
     */
    abstract public function handle(): mixed;
}
