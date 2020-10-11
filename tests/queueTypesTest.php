<?php

declare(strict_types = 1);

use const Folded\QUEUE_TYPE_FIFO;
use const Folded\QUEUE_TYPE_FILO;

it("should have the queue type fifo", function (): void {
    expect(QUEUE_TYPE_FIFO)->toBe("fifo");
});

it("should have the queue type filo", function (): void {
    expect(QUEUE_TYPE_FILO)->toBe("filo");
});
