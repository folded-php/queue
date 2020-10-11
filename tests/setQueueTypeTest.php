<?php

declare(strict_types = 1);

use Folded\Queue;
use const Folded\QUEUE_TYPE_FIFO;
use function Folded\setQueueType;

afterEach(function (): void {
    Queue::clear();
});

it("should return null when calling setQueueType", function (): void {
    expect(setQueueType(QUEUE_TYPE_FIFO))->toBeNull();
});
