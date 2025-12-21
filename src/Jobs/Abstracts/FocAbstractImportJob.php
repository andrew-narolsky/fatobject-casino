<?php

namespace FOC\Jobs\Abstracts;

/**
 * FocAbstractImportJob
 *
 * Abstracts job for paginated import processes.
 * Handles queue preparation and dispatching logic.
 */
abstract class FocAbstractImportJob extends FocAbstractJob
{
    /**
     * Post type to be imported.
     */
    abstract protected static function postType(): string;

    /**
     * Background process class.
     */
    abstract protected static function processClass(): string;

    /**
     * Status option name.
     */
    protected static function statusOption(): string
    {
        return static::STATUS_OPTION;
    }

    /**
     * Handle the job execution.
     *
     * Initializes the slot sync background process, adds a task
     * to the queue, and dispatches it for asynchronous processing.
     */
    public static function handle(): void
    {
        update_option(static::statusOption(), [
            'status' => 'queued',
        ]);

        $perPage = 10;
        $postType = static::postType();

        $counts = wp_count_posts($postType);

        $count = (int) (
            ($counts->publish ?? 0) +
            ($counts->draft ?? 0) +
            ($counts->trash ?? 0)
        );

        if ($count === 0) {
            error_log(sprintf(
                '%s skipped: no %s posts found.',
                static::class,
                $postType
            ));
            return;
        }

        $totalPages = (int)ceil($count / $perPage);
        $processClass = static::processClass();

        $process = new $processClass();

        for ($page = 1; $page <= $totalPages; $page++) {
            $process->push_to_queue([
                'page' => $page,
                'total' => $totalPages,
            ]);
        }

        $process->save()->dispatch();
    }
}