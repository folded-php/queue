<?php

declare(strict_types = 1);

use const Folded\QUEUE_DRIVER_FILE;

it("should have the file queue driver", function (): void {
    expect(QUEUE_DRIVER_FILE)->toBe("file");
});
