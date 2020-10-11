<?php

declare(strict_types = 1);

use Folded\Queue;
use const Folded\QUEUE_DRIVER_FILE;
use function Folded\setQueueDriver;

afterEach(function (): void {
    Queue::clear();
});

it("should return null when calling setQueueDriver", function (): void {
    expect(setQueueDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]))->toBeNull();
});
