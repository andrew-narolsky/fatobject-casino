<?php

namespace FOC\Background\Abstracts;

use stdClass;
use WP_Error;

/**
 * WP Background Process
 *
 * @package WP-Background-Processing
 */
abstract class FocAbstractBackgroundProcess extends FocAbstractAsyncRequest
{
    /**
     * The default query arg name used for passing the chain ID to new processes.
     */
    const string CHAIN_ID_ARG_NAME = 'chain_id';

    /**
     * Unique background process chain ID.
     */
    private string $chain_id;

    /**
     * Action
     */
    protected string $action = 'background_process';

    /**
     * Start time of the current process.
     */
    protected int $start_time = 0;

    /**
     * Cron_hook_identifier
     */
    protected string $cron_hook_identifier;

    /**
     * Cron_interval_identifier
     */
    protected string $cron_interval_identifier;

    /**
     * Restrict object instantiation when using unserialize.
     */
    protected array|bool $allowed_batch_data_classes = true;

    /**
     * The status set when a process is cancelling.
     */
    const int STATUS_CANCELLED = 1;

    /**
     * The status set when a process is paused or pausing.
     */
    const int STATUS_PAUSED = 2;

    /**
     * Job class whose status will be updated during sync.
     */
    abstract protected function statusJob(): string;

    /**
     * Job class to be triggered when the sync is complete.
     */
    abstract protected function nextJob(): string;

    /**
     * Post type slug.
     */
    abstract protected function postType(): string;

    /**
     * Meta-key to match API items to posts.
     */
    abstract protected function metaKey(): string;

    /**
     * Define which API strategy should be used by this process.
     *
     * The returned class must implement {@see FocApiInterface} and represents
     * the concrete API client that will be initialized by {@see FocApiAwareTrait}.
     *
     * This allows the same background process logic to be reused with
     * different API endpoints by simply changing the strategy.
     */
    abstract protected function apiStrategy(): string;

    /**
     * Initiate a new background process.
     */
    public function __construct($allowed_batch_data_classes = true)
    {
        parent::__construct();

        if (empty($allowed_batch_data_classes) && false !== $allowed_batch_data_classes) {
            $allowed_batch_data_classes = true;
        }

        if (!is_bool($allowed_batch_data_classes) && !is_array($allowed_batch_data_classes)) {
            $allowed_batch_data_classes = true;
        }

        // If allowed_batch_data_classes property set in subclass,
        // only apply override if not allowing any class.
        if (true === $this->allowed_batch_data_classes || true !== $allowed_batch_data_classes) {
            $this->allowed_batch_data_classes = $allowed_batch_data_classes;
        }

        $this->cron_hook_identifier = $this->identifier . '_cron';
        $this->cron_interval_identifier = $this->identifier . '_cron_interval';

        add_action($this->cron_hook_identifier, [$this, 'handle_cron_healthcheck']);
        add_filter('cron_schedules', [$this, 'schedule_cron_healthcheck']);

        // Ensure dispatch query args included extra data.
        add_filter($this->identifier . '_query_args', [$this, 'filter_dispatch_query_args']);
    }

    /**
     * Schedule the cron health check and dispatch an async request to start processing the queue.
     */
    public function dispatch(): WP_Error|false|array
    {
        if ($this->is_processing()) {
            // Process already running.
            return false;
        }

        /**
         * Filter fired before a background process dispatches its next process.
         */
        $cancel = apply_filters($this->identifier . '_pre_dispatch', false, $this->get_chain_id());

        if ($cancel) {
            return false;
        }

        // Schedule the cron health check.
        $this->schedule_event();

        // Perform remote post.
        return parent::dispatch();
    }

    /**
     * Push to the queue.
     *
     * Note, save must be called to persist queued items to a batch for processing.
     */
    public function push_to_queue($data): static
    {
        $this->data[] = $data;

        return $this;
    }

    /**
     * Save the queued items for future processing.
     */
    public function save(): static
    {
        $key = $this->generate_key();

        if (!empty($this->data)) {
            update_site_option($key, $this->data);
        }

        // Clean out data so that new data isn't prepended with closed session's data.
        $this->data = [];

        return $this;
    }

    /**
     * Update a batch's queued items.
     */
    public function update($key, $data): static
    {
        if (!empty($data)) {
            update_site_option($key, $data);
        }

        return $this;
    }

    /**
     * Delete a batch of queued items.
     */
    public function delete($key): static
    {
        delete_site_option($key);

        return $this;
    }

    /**
     * Delete the entire job queue.
     */
    public function delete_all(): void
    {
        $batches = $this->get_batches();

        foreach ($batches as $batch) {
            $this->delete($batch->key);
        }

        delete_site_option($this->get_status_key());

        $this->cancelled();
    }

    /**
     * Cancel job on next batch.
     */
    public function cancel(): void
    {
        update_site_option($this->get_status_key(), self::STATUS_CANCELLED);

        // Just in case the job was paused at the time.
        $this->dispatch();
    }

    /**
     * Has the process been canceled?
     */
    public function is_cancelled(): bool
    {
        return $this->get_status() === self::STATUS_CANCELLED;
    }

    /**
     * Called when a background process has been canceled.
     */
    protected function cancelled(): void
    {
        do_action($this->identifier . '_cancelled', $this->get_chain_id());
    }

    /**
     * Pause job on the next batch.
     */
    public function pause(): void
    {
        update_site_option($this->get_status_key(), self::STATUS_PAUSED);
    }

    /**
     * Has the process been paused?
     */
    public function is_paused(): bool
    {
        return $this->get_status() === self::STATUS_PAUSED;
    }

    /**
     * Called when a background process has been paused.
     */
    protected function paused(): void
    {
        do_action($this->identifier . '_paused', $this->get_chain_id());
    }

    /**
     * Resume job.
     */
    public function resume(): void
    {
        delete_site_option($this->get_status_key());

        $this->schedule_event();
        $this->dispatch();
        $this->resumed();
    }

    /**
     * Called when a background process has been resumed.
     */
    protected function resumed(): void
    {
        do_action($this->identifier . '_resumed', $this->get_chain_id());
    }

    /**
     * Is queued?
     */
    public function is_queued(): bool
    {
        return !$this->is_queue_empty();
    }

    /**
     * Is the tool currently active, e.g., starting, working, paused, or cleaning up?
     */
    public function is_active(): bool
    {
        return $this->is_queued() || $this->is_processing() || $this->is_paused() || $this->is_cancelled();
    }

    /**
     * Generate a key for a batch.
     *
     * Generates a unique key based on microtime. Queue items are
     * given a unique key so that they can be merged upon save.
     */
    protected function generate_key($length = 64, $key = 'batch'): string
    {
        $unique = md5(microtime() . wp_rand());
        $prepend = $this->identifier . '_' . $key . '_';

        return substr($prepend . $unique, 0, $length);
    }

    /**
     * Get the status key.
     */
    protected function get_status_key(): string
    {
        return $this->identifier . '_status';
    }

    /**
     * Get the status value for the process.
     */
    protected function get_status(): int
    {
        global $wpdb;

        if (is_multisite()) {
            $status = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = %s AND site_id = %d LIMIT 1",
                    $this->get_status_key(),
                    get_current_network_id()
                )
            );
        } else {
            $status = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1",
                    $this->get_status_key()
                )
            );
        }

        return absint($status);
    }

    /**
     * Maybe process a batch of queued items.
     *
     * Checks whether data exists within the queue and that
     * the process is not yet running.
     */
    public function maybe_handle(): mixed
    {
        // Don't lock up other requests while processing.
        session_write_close();

        check_ajax_referer($this->identifier, 'nonce');

        // Background process already running.
        if ($this->is_processing()) {
            return $this->maybe_wp_die();
        }

        // Cancel requested.
        if ($this->is_cancelled()) {
            $this->clear_scheduled_event();
            $this->delete_all();

            return $this->maybe_wp_die();
        }

        // Pause requested.
        if ($this->is_paused()) {
            $this->clear_scheduled_event();
            $this->paused();

            return $this->maybe_wp_die();
        }

        // No data to process.
        if ($this->is_queue_empty()) {
            return $this->maybe_wp_die();
        }

        $this->handle();

        return $this->maybe_wp_die();
    }

    /**
     * Is the queue empty?
     */
    protected function is_queue_empty(): bool
    {
        return empty($this->get_batch());
    }

    /**
     * Is the background process currently running?
     */
    public function is_processing(): bool
    {
        if (get_site_transient($this->identifier . '_process_lock')) {
            // Process already running.
            return true;
        }

        return false;
    }

    /**
     * Lock the process.
     *
     * Lock the process so that multiple instances can't run simultaneously.
     * Override if applicable, but the duration should be greater than that
     * defined in the time_exceeded() method.
     */
    public function lock_process($reset_start_time = true): void
    {
        if ($reset_start_time) {
            $this->start_time = time(); // Set the start time of the current process.
        }

        $lock_duration = (property_exists($this, 'queue_lock_time')) ? $this->queue_lock_time : 60; // 1 minute
        $lock_duration = apply_filters($this->identifier . '_queue_lock_time', $lock_duration);

        $microtime = microtime();
        $locked = set_site_transient($this->identifier . '_process_lock', $microtime, $lock_duration);

        /**
         * Action to note whether the background process managed to create its lock.
         *
         * The lock is used to signify that a process is running a task and no other
         * process should be allowed to run the same task until the lock is released.
         */
        do_action(
            $this->identifier . '_process_locked',
            $locked,
            $microtime,
            $lock_duration,
            $this->get_chain_id()
        );
    }

    /**
     * Unlock the process.
     *
     * Unlock the process so that other instances can spawn.
     */
    protected function unlock_process(): static
    {
        $unlocked = delete_site_transient($this->identifier . '_process_lock');

        /**
         * Action to note whether the background process managed to release its lock.
         *
         * The lock is used to signify that a process is running a task, and no other
         * process should be allowed to run the same task until the lock is released.
         */
        do_action($this->identifier . '_process_unlocked', $unlocked, $this->get_chain_id());

        return $this;
    }

    /**
     * Get batch.
     */
    protected function get_batch(): stdClass|array
    {
        return array_reduce(
            $this->get_batches(1),
            static function ($carry, $batch) {
                return $batch;
            },
            []
        );
    }

    /**
     * Get batches.
     */
    public function get_batches($limit = 0): array
    {
        global $wpdb;

        if (empty($limit) || !is_int($limit)) {
            $limit = 0;
        }

        $table = $wpdb->options;
        $column = 'option_name';
        $key_column = 'option_id';
        $value_column = 'option_value';

        if (is_multisite()) {
            $table = $wpdb->sitemeta;
            $column = 'meta_key';
            $key_column = 'meta_id';
            $value_column = 'meta_value';
        }

        $key = $wpdb->esc_like($this->identifier . '_batch_') . '%';

        $sql = '
			SELECT *
			FROM ' . $table . '
			WHERE ' . $column . ' LIKE %s
			ORDER BY ' . $key_column . ' ASC
			';

        $args = [$key];

        if (!empty($limit)) {
            $sql .= ' LIMIT %d';

            $args[] = $limit;
        }

        $items = $wpdb->get_results(
            $wpdb->prepare(
                $sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $args
            )
        );

        $batches = [];

        if (!empty($items)) {
            $allowed_classes = $this->allowed_batch_data_classes;

            $batches = array_map(
                static function ($item) use ($column, $value_column, $allowed_classes) {
                    $batch = new stdClass();
                    $batch->key = $item->{$column};
                    $batch->data = static::maybe_unserialize($item->{$value_column}, $allowed_classes);

                    return $batch;
                },
                $items
            );
        }

        return $batches;
    }

    /**
     * Handle a dispatched request.
     *
     * Pass each queue item to the task handler while remaining
     * within server memory and time limit constraints.
     */
    public function handle(): mixed
    {
        $this->lock_process();

        /**
         * Number of seconds to sleep between batches. Defaults to 0 seconds, minimum 0.
         */
        $throttle_seconds = max(
            0,
            apply_filters(
                $this->identifier . '_seconds_between_batches',
                apply_filters(
                    $this->prefix . '_seconds_between_batches',
                    0
                )
            )
        );

        do {
            $batch = $this->get_batch();

            foreach ($batch->data as $key => $value) {
                $task = $this->task($value);

                if (false !== $task) {
                    $batch->data[$key] = $task;
                } else {
                    unset($batch->data[$key]);
                }

                // Keep the batch up to date while processing it.
                if (!empty($batch->data)) {
                    $this->update($batch->key, $batch->data);
                }

                // Let the server breathe a little.
                sleep($throttle_seconds);

                // Batch limits reached, or pause or cancel requested.
                if (!$this->should_continue()) {
                    break;
                }
            }

            // Delete current batch if fully processed.
            if (empty($batch->data)) {
                $this->delete($batch->key);
            }
        } while (!$this->is_queue_empty() && $this->should_continue());

        $this->unlock_process();

        // Start next batch or complete process.
        if (!$this->is_queue_empty()) {
            $this->dispatch();
        } else {
            $this->complete();
        }

        return $this->maybe_wp_die();
    }

    /**
     * Memory exceeded?
     *
     * Ensures the batch process never exceeds 90%
     * of the maximum WordPress memory.
     */
    protected function memory_exceeded(): bool
    {
        $memory_limit = $this->get_memory_limit() * 0.9; // 90% of max memory
        $current_memory = memory_get_usage(true);
        $return = false;

        if ($current_memory >= $memory_limit) {
            $return = true;
        }

        return apply_filters($this->identifier . '_memory_exceeded', $return);
    }

    /**
     * Get memory limit in bytes.
     */
    protected function get_memory_limit(): int
    {
        if (function_exists('ini_get')) {
            $memory_limit = ini_get('memory_limit');
        } else {
            // Sensible default.
            $memory_limit = '128M';
        }

        if (!$memory_limit || -1 === intval($memory_limit)) {
            // Unlimited, set to 32GB.
            $memory_limit = '32000M';
        }

        return wp_convert_hr_to_bytes($memory_limit);
    }

    /**
     * Time limit exceeded?
     *
     * Ensures the batch never exceeds a sensible time limit.
     * A timeout limit of 30s is common on shared hosting.
     */
    protected function time_exceeded(): bool
    {
        $finish = $this->start_time + apply_filters($this->identifier . '_default_time_limit', 20); // 20 seconds
        $return = false;

        if (time() >= $finish) {
            $return = true;
        }

        return apply_filters($this->identifier . '_time_exceeded', $return);
    }

    /**
     * Complete processing.
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete(): void
    {
        delete_site_option($this->get_status_key());

        // Remove the cron health check job from the cron schedule.
        $this->clear_scheduled_event();

        $this->completed();
    }

    /**
     * Called when a background process has completed.
     */
    protected function completed(): void
    {
        do_action($this->identifier . '_completed', $this->get_chain_id());
    }

    /**
     * Get the cron health check interval in minutes.
     *
     * Default is 5 minutes, minimum is 1 minute.
     */
    public function get_cron_interval(): int
    {
        $interval = 5;

        if (property_exists($this, 'cron_interval')) {
            $interval = $this->cron_interval;
        }

        $interval = apply_filters($this->cron_interval_identifier, $interval);

        return is_int($interval) && 0 < $interval ? $interval : 5;
    }

    /**
     * Schedule the cron health check job.
     */
    public function schedule_cron_healthcheck($schedules): mixed
    {
        $interval = $this->get_cron_interval();

        if (1 === $interval) {
            $display = __('Every Minute');
        } else {
            $display = sprintf(__('Every %d Minutes'), $interval);
        }

        // Adds an "Every NNN Minute(s)" schedule to the existing cron schedules.
        $schedules[$this->cron_interval_identifier] = [
            'interval' => MINUTE_IN_SECONDS * $interval,
            'display' => $display,
        ];

        return $schedules;
    }

    /**
     * Handle cron health check event.
     *
     * Restart the background process if not already running
     * and data exists in the queue.
     */
    public function handle_cron_healthcheck(): void
    {
        if ($this->is_processing()) {
            // Background process already running.
            exit;
        }

        if ($this->is_queue_empty()) {
            // No data to process.
            $this->clear_scheduled_event();
            exit;
        }

        $this->dispatch();
    }

    /**
     * Schedule the cron health check event.
     */
    protected function schedule_event(): void
    {
        if (!wp_next_scheduled($this->cron_hook_identifier)) {
            wp_schedule_event(
                time() + ($this->get_cron_interval() * MINUTE_IN_SECONDS),
                $this->cron_interval_identifier,
                $this->cron_hook_identifier
            );
        }
    }

    /**
     * Clear scheduled cron health check event.
     */
    protected function clear_scheduled_event(): void
    {
        $timestamp = wp_next_scheduled($this->cron_hook_identifier);

        if ($timestamp) {
            wp_unschedule_event($timestamp, $this->cron_hook_identifier);
        }
    }

    /**
     * Perform a task with a queued item.
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass-through. Or, return false to remove the
     * item from the queue.
     */
    abstract protected function task($item): mixed;

    /**
     * Maybe unserialize data, but not if an object.
     */
    protected static function maybe_unserialize($data, $allowed_classes): mixed
    {
        if (is_serialized($data)) {
            $options = [];
            if (is_bool($allowed_classes) || is_array($allowed_classes)) {
                $options['allowed_classes'] = $allowed_classes;
            }

            return @unserialize($data, $options); // @phpcs:ignore
        }

        return $data;
    }

    /**
     * Should any processing continue?
     */
    public function should_continue(): bool
    {
        /**
         * Filter whether the current background process should continue running the task
         * if there is data to be processed.
         *
         * If the processing time or memory limits have been exceeded, the value will be false.
         * If pause or cancel have been requested, the value will be false.
         *
         * It is very unlikely that you would want to override a false value with true.
         *
         * If false is returned here, it does not necessarily mean background processing is
         * complete. If there is batch data still to be processed and pause or cancel have not
         * been requested, it simply means this background process should spawn a new process
         * for the chain to continue processing and then close itself down.
         */
        return apply_filters(
            $this->identifier . '_should_continue',
            !($this->time_exceeded() || $this->memory_exceeded() || $this->is_paused() || $this->is_cancelled()),
            $this->get_chain_id()
        );
    }

    /**
     * Get the string used to identify this type of background process.
     */
    public function get_identifier(): string
    {
        return $this->identifier;
    }

    /**
     * Return the current background process chain's ID.
     *
     * If the chain's ID hasn't been set before this function is first used,
     * and hasn't been passed as a query arg during dispatch,
     * the chain ID will be generated before being returned.
     */
    public function get_chain_id(): string
    {
        if (empty($this->chain_id) && wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] === $this->identifier) {
            check_ajax_referer($this->identifier, 'nonce');

            if (!empty($_GET[$this->get_chain_id_arg_name()])) {
                $chain_id = sanitize_key($_GET[$this->get_chain_id_arg_name()]);

                if (wp_is_uuid($chain_id)) {
                    $this->chain_id = $chain_id;

                    return $this->chain_id;
                }
            }
        }

        if (empty($this->chain_id)) {
            $this->chain_id = wp_generate_uuid4();
        }

        return $this->chain_id;
    }

    /**
     * Filters the query arguments used during an async request.
     */
    public function filter_dispatch_query_args($args): array
    {
        $args[$this->get_chain_id_arg_name()] = $this->get_chain_id();

        return $args;
    }

    /**
     * Get the query arg name used for passing the chain ID to new processes.
     */
    private function get_chain_id_arg_name(): string
    {
        static $chain_id_arg_name;

        if (!empty($chain_id_arg_name)) {
            return $chain_id_arg_name;
        }

        /**
         * Filter the query arg name used for passing the chain ID to new processes.
         *
         * If you encounter problems with using the default query arg name, you can
         * change it with this filter.
         */
        $chain_id_arg_name = apply_filters($this->identifier . '_chain_id_arg_name', self::CHAIN_ID_ARG_NAME);

        if (!is_string($chain_id_arg_name) || empty($chain_id_arg_name)) {
            $chain_id_arg_name = self::CHAIN_ID_ARG_NAME;
        }

        return $chain_id_arg_name;
    }
}