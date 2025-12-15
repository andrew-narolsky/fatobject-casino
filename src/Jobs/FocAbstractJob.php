<?php

namespace FOC\Jobs;

/**
 * AbstractJob
 *
 * Base abstract class for background jobs.
 *
 * Provides common methods for initializing background processes,
 * adding tasks to the queue, and dispatching them asynchronously.
 *
 * Child classes should implement the `handle()` method with
 * job-specific logic
 */
abstract class FocAbstractJob
{
    /**
     * Status option name stored in the database.
     *
     * Used to track the progress of the brand synchronization task.
     * The option stores an array with keys like:
     *  - status: 'queued' | 'running' | 'completed'
     *  - percent: integer (progress percentage)
     *  - processed: integer (items processed)
     *  - total: integer (total items)
     */
    public const string STATUS_OPTION = '';

    /**
     * Handle the job execution.
     *
     * Initializes the brand sync background process, adds a task
     * to the queue, and dispatches it for asynchronous processing.
     */
    abstract public static function handle(): void;
}
