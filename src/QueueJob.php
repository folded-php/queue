<?php

declare(strict_types = 1);

namespace Folded;

/**
 * Data object that represents a job queue.
 *
 * @since 0.1.0
 */
final class QueueJob
{
    /**
     * The payload attached to the job queue.
     *
     * @var array<mixed>
     *
     * @since 0.1.0
     */
    private array $payload;

    /**
     * The type of job queue to help the user classify the jobs.
     *
     * @since 0.1.0
     */
    private string $type;

    /**
     * Constructor.
     *
     * @param string       $type    The type of job queue.
     * @param array<mixed> $payload The payload attached to the job queue.
     *
     * @since 0.1.0
     *
     * @example
     * $job = new QueueJob("account-created", ["email" => "john@doe.com"]);
     */
    public function __construct(string $type, array $payload)
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    /**
     * Get the payload.
     *
     * @return array<mixed>
     *
     * @since 0.1.0
     *
     * @example
     * $job = new QueueJob("account-created", ["email" => "john@doe.com"]);
     * $payload = $job->getPayload();
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Get the type.
     *
     * @since 0.1.0
     *
     * @example
     * $job = new QueueJob("account-created", ["email" => "john@doe.com"]);
     * $type = $job->getType();
     */
    public function getType(): string
    {
        return $this->type;
    }
}
