<?php

declare(strict_types = 1);

namespace Folded;

use Exception;
use Webmozart\Assert\Assert;
use Folded\QueueDrivers\FileQueueDriver;
use Folded\QueueDrivers\SqliteQueueDriver;

/**
 * Represent a task that is stored for later processing.
 *
 * @since 0.1.0
 */
final class Queue
{
    /**
     * @since 0.1.0
     */
    const DEFAULT_CHANNEL = "default";

    /**
     * The driver to use to store and get the queues.
     * Default to the file driver.
     *
     * @since 0.1.0
     * @see src/queueDrivers.php for a list of available queue drivers.
     */
    private static string $driver = QUEUE_DRIVER_FILE;

    /**
     * The parameters that are used to instanciate the driver.
     *
     * @var array<mixed>
     *
     * @since 0.1.0
     */
    private static array $driverParameters = [];

    /**
     * The driver instance used to add and get queue jobs.
     *
     * @since 0.1.0
     */
    private static ?QueueDriverInterface $queueDriver = null;

    /**
     * The type of queue retreival strategy to use.
     * Default to the FIFO type.
     *
     * @since 0.1.0
     * @see src/queueTypes.php for a list of available queue types.
     */
    private static string $type = QUEUE_TYPE_FIFO;

    /**
     * Add a job to the queue.
     *
     * @param string       $type    A type to classify the job.
     * @param array<mixed> $payload An optional payload to attach to the job.
     * @param string       $channel The channel to add the job queue on. Default: "default".
     *
     * @since 0.1.0
     *
     * @example
     * Queue::setDriver("file", ["folder" => "path/to/folder"]);
     * Queue::addJob("account-created", ["email" => "john@doe.com"]);
     */
    public static function addJob(string $type, array $payload = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::checkDriver();
        self::getDriver()->addJob($type, $payload, $channel);
    }

    /**
     * Clear the state of this class.
     * Useful for unit testing.
     *
     * @since 0.1.0
     *
     * @example
     * Queue::clear();
     */
    public static function clear(): void
    {
        self::$driver = QUEUE_DRIVER_FILE;
        self::$driverParameters = [];
        self::$type = QUEUE_TYPE_FIFO;
    }

    /**
     * Get a job from the queue.
     *
     * @param string $channel The channel to get the job from. Default: "default".
     *
     * @since 0.1.0
     *
     * @example
     * Queue::setDriver("file", ["folder" => "path/to/folder"]);
     * $job = Queue::getJob();
     */
    public static function getJob(string $channel = self::DEFAULT_CHANNEL): QueueJob
    {
        return self::getDriver()->getJob($channel);
    }

    /**
     * Returns true if the queue has at least one job, else returns false.
     *
     * @param string $channel The channel to check the job on.
     *
     * @since 0.1.0
     *
     * @example
     * Queue::setDriver("file", ["folder" => "path/to/folder"]);
     *
     * if (Queue::hasJob()) {
     *  echo "queue has a job";
     * } else {
     *  echo "no job on queue";
     * }
     */
    public static function hasJob(string $channel = self::DEFAULT_CHANNEL): bool
    {
        return self::getDriver()->hasJob($channel);
    }

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
     * Queue::setDriver("file", ["folder" => "path/to/folder"]);
     */
    public static function setDriver(string $driver, array $parameters): void
    {
        self::$queueDriver = null;
        self::$driver = $driver;
        self::$driverParameters = $parameters;

        self::checkDriver();
        self::checkDriverParameters();
    }

    /**
     * Set the type of job queue retreival.
     *
     * @param string $type The type of job queue retreival.
     *
     * @since 0.1.0
     * @see src/queueTypes.php for a list of available queue types.
     *
     * @example
     * Queue::setType("fifo");
     */
    public static function setType(string $type): void
    {
        Assert::inArray($type, SUPPORTED_QUEUE_TYPES);

        self::$queueDriver = null;
        self::$type = $type;
    }

    /**
     * Check if the driver is allowed.
     *
     * @since 0.1.0
     *
     * @example
     * Queue::checkDriver();
     */
    private static function checkDriver(): void
    {
        Assert::inArray(self::$driver, SUPPORTED_QUEUE_DRIVERS);
    }

    /**
     * Calls the specific driver to verify the parameters.
     *
     * @since 0.1.0
     *
     * @example
     * Queue::checkDriverParameters();
     */
    private static function checkDriverParameters(): void
    {
        switch (self::$driver) {
            case QUEUE_DRIVER_FILE:
                FileQueueDriver::checkParameters(self::$driverParameters);

                break;
            case QUEUE_DRIVER_SQLITE:
                SqliteQueueDriver::checkParameters(self::$driverParameters);

                break;
            default:
                throw new Exception("unsupported driver " . self::$driver);
        }
    }

    /**
     * Get the driver. If it has been instanciated, it is returned (acting as a singleton).
     *
     * @since 0.1.0
     *
     * @example
     * $driver = Queue::getDriver();
     */
    private static function getDriver(): QueueDriverInterface
    {
        switch (self::$driver) {
            case QUEUE_DRIVER_FILE:
                return self::$queueDriver instanceof FileQueueDriver ? self::$queueDriver : new FileQueueDriver(self::$driverParameters["folder"], self::$type);
            case QUEUE_DRIVER_SQLITE:
                return  self::$queueDriver instanceof SqliteQueueDriver ? self::$queueDriver : new SqliteQueueDriver(
                    self::$type,
                    self::$driverParameters["pdo"],
                    self::$driverParameters["table"]["name"],
                    self::$driverParameters["table"]["columns"]["id"],
                    self::$driverParameters["table"]["columns"]["type"],
                    self::$driverParameters["table"]["columns"]["payload"],
                    self::$driverParameters["table"]["columns"]["channel"]
                );
            default:
               throw new Exception("unsupported driver " . self::$driver);
        }
    }
}
