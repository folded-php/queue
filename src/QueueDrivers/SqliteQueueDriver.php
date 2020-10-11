<?php

declare(strict_types = 1);

namespace Folded\QueueDrivers;

use PDO;
use Exception;
use Folded\Queue;
use PDOStatement;
use Folded\QueueJob;
use Webmozart\Assert\Assert;
use Doctrine\DBAL\DriverManager;
use Folded\QueueDriverInterface;
use const Folded\QUEUE_TYPE_FIFO;
use Folded\Traits\CanManipulateJson;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * A driver to handle SQLite stored job queues.
 *
 * @since 0.1.0
 */
final class SqliteQueueDriver implements QueueDriverInterface
{
    // @since 0.1.0
    use CanManipulateJson;

    /**
     * The channel to add and get the job queues from.
     *
     * @since 0.1.0
     */
    private string $channel;

    /**
     * The name of the channel column in the jobs table.
     *
     * @since 0.1.0
     */
    private string $channelColumnName;

    /**
     * The name of the id column in the jobs table.
     *
     * @since 0.1.0
     */
    private string $idColumnName;

    /**
     * The name of the payload column in the jobs table.
     *
     * @since 0.1.0
     */
    private string $payloadColumnName;

    /**
     * The PDO connection to get and insert the jobs.
     *
     * @since 0.1.0
     */
    private PDO $pdo;

    /**
     * The type of queue, to get and insert jobs.
     *
     * @since 0.1.0
     * @see src/QueueTypes.php for a list of available queue types.
     */
    private string $queueType;

    /**
     * The table name to get and store jobs on.
     *
     * @since 0.1.0
     */
    private string $tableName;

    /**
     * The name of the type column in the jobs table.
     * The type is used to help the user classify jobs.
     *
     * @since 0.1.0
     */
    private string $typeColumnName;

    /**
     * Constructor.
     *
     * @param string $queueType         The type of queue retreival strategy.
     * @param PDO    $pdo               The PDO connection to use to store and get jobs.
     * @param string $tableName         The table name to store and get jobs from.
     * @param string $idColumnName      The id column name in the jobs table.
     * @param string $payloadColumnName The payload column name in the jobs table.
     * @param string $channelColumnName The channel column name in the jobs table.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new SqliteQueueDriver("fifo", new PDO(), "jobs", "id", "type", "payload", "channel");
     */
    public function __construct(string $queueType, PDO $pdo, string $tableName, string $idColumnName, string $typeColumnName, string $payloadColumnName, string $channelColumnName)
    {
        $this->queueType = $queueType;
        $this->channel = Queue::DEFAULT_CHANNEL;
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->idColumnName = $idColumnName;
        $this->typeColumnName = $typeColumnName;
        $this->payloadColumnName = $payloadColumnName;
        $this->channelColumnName = $channelColumnName;
    }

    /**
     * Add a job in the job table.
     *
     * @param string       $type    The type of job (to help the user classify jobs).
     * @param array<mixed> $payload The JSON convertible payload to attach to the job.
     * @param string       $channel The channel to add the job to.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new SqliteQueueDriver("fifo", new PDO(), "jobs", "id", "type", "payload", "channel");
     * $driver->addJob("account-created", ["email" => "john@doe.com"], "default");
     */
    public function addJob(string $type, array $payload, string $channel): void
    {
        $databaseEngine = $this->getDatabaseEngine();
        $payload = self::arrayToJsonString($payload);

        $databaseEngine->insert($this->tableName)
            ->setValue($this->typeColumnName, "?")
            ->setValue($this->payloadColumnName, "?")
            ->setValue($this->channelColumnName, "?")
            ->setParameter(0, $type)
            ->setParameter(1, $payload)
            ->setParameter(2, $channel)
            ->execute();
    }

    /**
     * Checks if an array of parameters is valid to instanciate the driver.
     *
     * @param array<mixed> $parameters The driver parameters.
     *
     * @since 0.1.0
     *
     * @example
     * SqliteQueueDriver::checkParameters([]);
     */
    public static function checkParameters(array $parameters): void
    {
        Assert::keyExists($parameters, "pdo");
        Assert::isInstanceOf($parameters["pdo"], PDO::class);
        Assert::keyExists($parameters, "table");
        Assert::isArray($parameters["table"]);
        Assert::keyExists($parameters["table"], "name");
        Assert::string($parameters["table"]["name"]);
        Assert::keyExists($parameters["table"], "columns");
        Assert::keyExists($parameters["table"]["columns"], "id");
        Assert::string($parameters["table"]["columns"]["id"]);
        Assert::keyExists($parameters["table"]["columns"], "type");
        Assert::string($parameters["table"]["columns"]["type"]);
        Assert::keyExists($parameters["table"]["columns"], "payload");
        Assert::string($parameters["table"]["columns"]["payload"]);
        Assert::keyExists($parameters["table"]["columns"], "channel");
        Assert::string($parameters["table"]["columns"]["channel"]);
    }

    /**
     * Get a job from the job table.
     *
     * @param string $channel The channel to get the job queue from.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new SqliteQueueDriver("fifo", new PDO(), "jobs", "id", "type", "payload", "channel");
     * $job = $driver->getJob("default");
     */
    public function getJob(string $channel): QueueJob
    {
        $this->channel = $channel;
        [$type, $payload] = $this->getJobData();

        return new QueueJob($type, self::jsonStringToArray($payload));
    }

    /**
     * Returns true if there is at least one job in the job table for the given channel, else returns false.
     *
     * @param string $channel The channel to filter the jobs on.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new SqliteQueueDriver("fifo", new PDO(), "jobs", "id", "type", "payload", "channel");
     *
     * if ($driver->hasJob("default")) {
     *  echo "jobs exists in the default channel";
     * } else {
     *  echo "no jobs found in the default channel";
     * }
     */
    public function hasJob(string $channel): bool
    {
        $this->channel = $channel;
        $databaseEngine = $this->getDatabaseEngine();

        $statement = $databaseEngine->select("COUNT(1)")
            ->from($this->tableName)
            ->setMaxResults(1)
            ->execute();

        return $statement instanceof PDOStatement && $statement->fetchColumn() > 0;
    }

    /**
     * Returns the query builder to perform query on the SQLite table.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new SqliteQueueDriver("fifo", new PDO(), "jobs", "id", "type", "payload", "channel");
     *
     * $databaseEngine = $driver->getDatabaseEngine();
     */
    private function getDatabaseEngine(): QueryBuilder
    {
        // Ignoring because an issue exists
        // see https://github.com/phpstan/phpstan-doctrine/issues/101
        // @phpstan-ignore-next-line
        return DriverManager::getConnection([
            "driver" => "pdo_sqlite",
            "pdo" => $this->pdo,
        ])->createQueryBuilder();
    }

    /**
     * Get the type and the payload attached to a job.
     *
     * @return array<string>
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new SqliteQueueDriver("fifo", new PDO(), "jobs", "id", "type", "payload", "channel");
     *
     * [$type, $payload] = $driver->getJobData();
     */
    private function getJobData(): array
    {
        $databaseEngine = $this->getDatabaseEngine();

        // The returned type is PDOStatement, but the static analysis (as well as VSCode) identify Doctrine's ResultStatement|int result.
        // Ignoring this one.
        // @phpstan-ignore-next-line
        $data = $databaseEngine->select([
            $this->idColumnName,
            $this->typeColumnName,
            $this->payloadColumnName,
        ])->from($this->tableName)
            ->where("{$this->channelColumnName} = ?")
            ->setParameter(0, $this->channel)
            ->orderBy($this->idColumnName, $this->queueType === QUEUE_TYPE_FIFO ? "asc" : "desc")
            ->setMaxResults(1)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);

        if (empty($data)) {
            throw new Exception("no job found");
        }

        $databaseEngine->delete($this->tableName)
            ->where("{$this->idColumnName} = ?")
            ->setParameter(0, $data["id"])
            ->execute();

        return [$data["type"], $data["payload"]];
    }
}
