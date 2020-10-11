<?php

declare(strict_types = 1);

namespace Folded;

/**
 * @since 0.1.0
 */
const QUEUE_DRIVER_FILE = "file";

/**
 * @since 0.1.0
 */
const QUEUE_DRIVER_SQLITE = "sqlite";

/**
 * @since 0.1.0
 */
const SUPPORTED_QUEUE_DRIVERS = [
    QUEUE_DRIVER_FILE,
    QUEUE_DRIVER_SQLITE,
];
