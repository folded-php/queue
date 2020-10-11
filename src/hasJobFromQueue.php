<?php

declare(strict_types = 1);

namespace Folded;

if (!function_exists("Folded\hasJobFromQueue")) {
    /**
     * Returns true if the queue has at least one job, else returns false.
     *
     * @param string $channel The channel to check the job on.
     *
     * @since 0.1.0
     *
     * @example
     * setQueueDriver("file", ["folder" => "path/to/folder"]);
     *
     * if (hasJobFromQueue()) {
     *  echo "queue has a job";
     * } else {
     *  echo "no job on queue";
     * }
     */
    function hasJobFromQueue(string $channel = Queue::DEFAULT_CHANNEL): bool
    {
        return Queue::hasJob($channel);
    }
}
