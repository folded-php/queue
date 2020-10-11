<?php

declare(strict_types = 1);

use Folded\Queue;
use function Folded\addJobToQueue;
use const Folded\QUEUE_DRIVER_FILE;
use function Folded\setQueueDriver;
use function Folded\getJobFromQueue;

afterEach(function (): void {
    $default = __DIR__ . "/misc/jobs/default.job";

    if (file_exists($default)) {
        unlink($default);
    }

    Queue::clear();
});

it("should get the job from the queue when calling getJobFromQueue", function (): void {
    $type = "foo";
    setQueueDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);

    addJobToQueue($type);

    expect(getJobFromQueue()->getType())->toBe($type);
});
