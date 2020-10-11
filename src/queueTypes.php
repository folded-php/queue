<?php

declare(strict_types = 1);

namespace Folded;

/**
 * Represents First In, First Out queue retreival strategy.
 * If a job A is added, then a job B is added, when getting the job, it's the first job (A) that should be returned.
 *
 * @since 0.1.0
 */
const QUEUE_TYPE_FIFO = "fifo";

/**
 * Represents First In, Last Out queue retreival strategy.
 * If a job A is added, then a job B is added, when getting the job, it's the last job (B) that should be returned.
 *
 * @since 0.1.0
 */
const QUEUE_TYPE_FILO = "filo";

/**
 * @since 0.1.0
 */
const SUPPORTED_QUEUE_TYPES = [
    QUEUE_TYPE_FIFO,
    QUEUE_TYPE_FILO,
];
