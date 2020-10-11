<?php

declare(strict_types = 1);

use Folded\Queue;
use const Folded\QUEUE_DRIVER_FILE;
use function Folded\hasJobFromQueue;
use function Folded\setQueueDriver;

afterEach(function (): void {
    $default = __DIR__ . "/misc/jobs/default.job";

    if (file_exists($default)) {
        unlink($default);
    }

    Queue::clear();
});

it("should return false if no job has been created before calling hasJobFromQueue", function (): void {
    setQueueDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    expect(hasJobFromQueue())->toBeFalse();
});
