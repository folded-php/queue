<?php

declare(strict_types = 1);

namespace Folded;

if (!function_exists("Folded\setQueueDriver")) {
    /**
     * Set the driver as well as the necessary parameters.
     *
     * @param string       $driver     The driver to use.
     * @param array<mixed> $parameters The parameters to use to instanciate the driver.
     *
     * @since 0.1.0
     * @see src/queueDrivers.php for a list of available drivers.
     *
     * @example
     * setQueueDriver("file", ["folder" => "path/to/folder"]);
     */
    function setQueueDriver(string $driver, array $parameters): void
    {
        Queue::setDriver($driver, $parameters);
    }
}
