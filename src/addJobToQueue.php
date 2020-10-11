<?php

declare(strict_types = 1);

namespace Folded;

if (!function_exists("Folded\addJobToQueue")) {
    /**
     * Add a job to the queue.
     *
     * @param string       $type    A type to classify the job.
     * @param array<mixed> $payload An optional payload to attach to the job.
     * @param string       $channel The channel to add the job queue on. Default: "default".
     *
     * @since 0.1.0
     *
     * @example
     * setQueueDriver("file", ["folder" => "path/to/folder"]);
     * addJobToQueue("account-created", ["email" => "john@doe.com"]);
     */
    function addJobToQueue(string $type, array $payload = [], string $channel = Queue::DEFAULT_CHANNEL): void
    {
        Queue::addJob($type, $payload, $channel);
    }
}
