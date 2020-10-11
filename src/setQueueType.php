<?php

declare(strict_types = 1);

namespace Folded;

if (!function_exists("setQueueType")) {
    /**
     * Set the type of job queue retreival.
     *
     * @param string $type The type of job queue retreival.
     *
     * @since 0.1.0
     * @see src/queueTypes.php for a list of available queue types.
     *
     * @example
     * setQueueType("fifo");
     */
    function setQueueType(string $type): void
    {
        Queue::setType($type);
    }
}
