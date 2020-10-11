<?php

declare(strict_types = 1);

namespace Folded;

/**
 * Drives the implementations of the drivers supporting queue management, like file, sqlite, ...
 *
 * @since 0.1.0
 */
interface QueueDriverInterface
{
    /**
     * Adds a job in the given queue.
     *
     * @param string       $type    The kind of job. This is your personal way to classify jobs.
     * @param array<mixed> $payload The payload to pass to the job (must be convertible to JSON).
     * @param string       $channel The channel to identify different job queues.
     *
     * @since 0.1.0
     */
    public function addJob(string $type, array $payload, string $channel): void;

    /**
     * Should throw exceptions if the parameters are not the expected one for the driver.
     *
     * @param array<mixed> $parameters The driver parameters.
     *
     * @since 0.1.0
     */
    public static function checkParameters(array $parameters): void;

    /**
     * Get the job convert as a QueueJob.
     *
     * @param string $channel The channel from which to get the job queue.
     *
     * @since 0.1.0
     */
    public function getJob(string $channel): QueueJob;

    /**
     * Returns true if at least one job is present in the queue, else returns false.
     *
     * @param string $channel The channel to check the job queue on.
     *
     * @since 0.1.0
     */
    public function hasJob(string $channel): bool;
}
