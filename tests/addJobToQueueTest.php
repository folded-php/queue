<?php

declare(strict_types = 1);

use Folded\Queue;
use function Folded\addJobToQueue;
use const Folded\QUEUE_DRIVER_FILE;
use function Folded\setQueueDriver;

afterEach(function (): void {
    $default = __DIR__ . "/misc/jobs/default.job";

    if (file_exists($default)) {
        unlink($default);
    }

    Queue::clear();
});

it("should return null when calling addJobToQueue", function (): void {
    setQueueDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);

    expect(addJobToQueue("foo"))->toBeNull();
});
