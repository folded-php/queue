<?php

declare(strict_types = 1);

namespace Folded;

if (!function_exists("Folded\getJobFromQueue")) {
    /**
     * Get a job from the queue.
     *
     * @param string $channel The channel to get the job from. Default: "default".
     *
     * @since 0.1.0
     *
     * @example
     * setQueueDriver("file", ["folder" => "path/to/folder"]);
     * $job = getJobFromQueue();
     */
    function getJobFromQueue(string $channel = Queue::DEFAULT_CHANNEL): QueueJob
    {
        return Queue::getJob($channel);
    }
}
