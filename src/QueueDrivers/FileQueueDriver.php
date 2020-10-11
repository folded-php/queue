<?php

declare(strict_types = 1);

namespace Folded\QueueDrivers;

use Exception;
use Folded\Queue;
use Folded\QueueJob;
use Webmozart\Assert\Assert;
use function Folded\openFile;
use function Folded\closeFile;
use function Folded\changeName;
use Folded\QueueDriverInterface;
use const Folded\QUEUE_TYPE_FIFO;
use const Folded\QUEUE_TYPE_FILO;
use Folded\Traits\CanManipulateJson;
use function Folded\addCsvRowToFile;
use function Folded\getCsvRowFromFile;

/**
 * A driver to handle job queue using files on disk.
 *
 * @since 0.1.0
 */
final class FileQueueDriver implements QueueDriverInterface
{
    // @since 0.1.0
    use CanManipulateJson;

    /**
     * For this driver, the channel is the name of the file in which the jobs are stored.
     *
     * @since 0.1.0
     */
    private string $channel;

    /**
     * The path to the folder containing the job files. The job files have the extension ".job".
     *
     * @since 0.1.0
     */
    private string $folder;

    /**
     * The type of queue strategy to get the job queues.
     *
     * @since 0.1.0
     * @see src/queueTypes.php for a list of supported queue types.
     */
    private string $queueType;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new FileQueueDriver("path/to/folder", "fifo");
     */
    public function __construct(string $folder, string $queueType)
    {
        $this->folder = $folder;
        $this->queueType = $queueType;
        $this->channel = Queue::DEFAULT_CHANNEL;
    }

    /**
     * Adds a job to the given queue.
     *
     * @param string       $type    A category to help you filter on the job queues you stored.
     * @param array<mixed> $payload Data to stores alongside the job queue.
     * @param string       $channel The name of the channel to add the job queue on.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new FileQueueDriver("path/to/folder", "fifo");
     * $driver->addJob("account-created", ["email" => "john@doe.com"], "default");
     */
    public function addJob(string $type, array $payload, string $channel): void
    {
        $this->channel = $channel;
        $filePath = $this->getJobFilePath();
        $jsonString = self::arrayToJsonString($payload);

        $file = openFile($filePath, "a");

        addCsvRowToFile($file, [
            $type,
            $jsonString,
        ]);

        closeFile($file);
    }

    /**
     * Check if the driver parameters are correct.
     *
     * @param array<mixed> $parameters The driver parameters
     *
     * @since 0.1.0
     *
     * @example
     * FileQueueDriver::checkParameters(["folder" => "path/to/folder"]);
     */
    public static function checkParameters(array $parameters): void
    {
        Assert::keyExists($parameters, "folder");
        Assert::string($parameters["folder"]);
        Assert::directory($parameters["folder"]);
    }

    /**
     * Get a job from a given queue.
     *
     * @param string $channel The channel to get the job from.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new FileQueueDriver("path/to/folder", "fifo);
     * $job = $driver->getJob("default");
     */
    public function getJob(string $channel): QueueJob
    {
        if (!$this->hasJob($channel)) {
            throw new Exception("no job found");
        }

        $this->channel = $channel;

        [$type, $payload] = $this->getJobData();

        return new QueueJob($type, self::jsonStringToArray($payload));
    }

    /**
     * Returns true if at least one job is present in the given channel, else returns false.
     *
     * @param string $channel The channel from which to seek for a job.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new FileQueueDriver("path/to/folder", "fifo");
     *
     * if ($driver->hasJob("default")) {
     *  echo "has some job in queue";
     * } else {
     *  echo "has no jobs in queue";
     * }
     */
    public function hasJob(string $channel): bool
    {
        $this->channel = $channel;
        $filePath = $this->getJobFilePath();

        clearstatcache();

        return file_exists($filePath) && filesize($filePath) > 0;
    }

    /**
     * Get the type and the payload from the queue job file.
     *
     * @since 0.1.0
     *
     * @return array<mixed>
     *
     * @example
     * $driver = new FileQueueDriver("path/fo/folder", ""fifo);
     * [$type, $payload] = $driver->getJobData();
     */
    private function getJobData(): array
    {
        $tempFilePath = $this->getTempJobFilePath();
        $filePath = $this->getJobFilePath();
        $tempFile = null;

        $file = openFile($filePath, "r");
        $data = "";

        switch ($this->queueType) {
            case QUEUE_TYPE_FIFO:
                $data = getCsvRowFromFile($file);
                $tempFile = openFile($tempFilePath, "w");

                while ($line = fgetcsv($file)) {
                    addCsvRowToFile($tempFile, $line);
                }

                break;
            case QUEUE_TYPE_FILO:
                $firstLine = getCsvRowFromFile($file);

                $rows = [$firstLine];

                while ($line = fgetcsv($file)) {
                    $rows[] = $line;
                }

                $data = array_pop($rows);

                $tempFile = openFile($tempFilePath, "w");

                if (count($rows) > 0) {
                    foreach ($rows as $row) {
                        addCsvRowToFile($tempFile, $row);
                    }
                }

                break;
        }

        closeFile($file);

        if (is_resource($tempFile)) {
            closeFile($tempFile);
        }

        changeName($tempFilePath, $filePath);

        // Ignoring, because type hinting this is complicated
        // @phpstan-ignore-next-line
        return $data;
    }

    /**
     * Get the job queue file path.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new FileQueueDriver("path/to/folder", "fifo");
     * $path = $driver->getJobFilePath();
     */
    private function getJobFilePath(): string
    {
        $directorySeparator = DIRECTORY_SEPARATOR;

        return "{$this->folder}$directorySeparator{$this->channel}.job";
    }

    /**
     * Get the temporary job queue file path.
     * It is used to transfer the rest of the job queues to the file, while poping the first or last queue job.
     *
     * @since 0.1.0
     *
     * @example
     * $driver = new FileQueueDriver("path/to/folder", "fifo");
     * $path = $driver->getTempJobFilePath();
     */
    private function getTempJobFilePath(): string
    {
        return "{$this->getJobFilePath()}.temp";
    }
}
