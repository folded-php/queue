<?php

declare(strict_types = 1);

use Folded\Queue;
use const Folded\QUEUE_DRIVER_FILE;
use const Folded\QUEUE_DRIVER_SQLITE;
use const Folded\QUEUE_TYPE_FIFO;
use const Folded\QUEUE_TYPE_FILO;

afterEach(function (): void {
    $paths = [
        __DIR__ . "/misc/jobs/default.job",
        __DIR__ . "/misc/jobs/custom.job",
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            $deleted = unlink($path);

            if ($deleted === false) {
                throw new Exception("cannot delete file $path");
            }
        }
    }

    $copied = copy(__DIR__ . "/misc/jobs/original.sqlite", __DIR__ . "/misc/jobs/database.sqlite");

    if ($copied === false) {
        throw new Exception("cannot copy original sqlite database to the new location");
    }

    Queue::clear();
});

it("should persist the job in default channel for file driver", function (): void {
    $folder = __DIR__ . "/misc/jobs";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => $folder,
    ]);

    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);

    expect(file_get_contents($folder . "/default.job"))->toBe('account-created,"{""email"":""john@doe.com""}"' . "\n");
});

it("should get the first added job in default channel for file driver in fifo mode", function (): void {
    $firstPayload = [
        "email" => "john@doe.com",
    ];
    $firstType = "account-created";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob($firstType, $firstPayload);
    Queue::addJob("password-reset", [
        "email" => "jane@northernwind.org",
    ]);

    $job = Queue::getJob();

    expect($job->getPayload())->toBe($firstPayload);
    expect($job->getType())->toBe($firstType);
});

it("should get the first added job in custom channel for file driver in fifo mode", function (): void {
    $firstPayload = [
        "email" => "john@doe.com",
    ];
    $firstType = "account-created";
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob($firstType, $firstPayload, $channel);
    Queue::addJob("password-reset", [
        "email" => "jane@northernwind.org",
    ], $channel);

    $job = Queue::getJob($channel);

    expect($job->getPayload())->toBe($firstPayload);
    expect($job->getType())->toBe($firstType);
});

it("should get the first added job in default channel for sqlite driver in fifo mode", function (): void {
    $firstPayload = [
        "email" => "john@doe.com",
    ];
    $firstType = "account-created";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob($firstType, $firstPayload);
    Queue::addJob("password-reset", [
        "email" => "jane@northernwind.org",
    ]);

    $job = Queue::getJob();

    expect($job->getPayload())->toBe($firstPayload);
    expect($job->getType())->toBe($firstType);
});

it("should get the first added job in custom channel for sqlite driver in fifo mode", function (): void {
    $firstPayload = [
        "email" => "john@doe.com",
    ];
    $firstType = "account-created";
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob($firstType, $firstPayload, $channel);
    Queue::addJob("password-reset", [
        "email" => "jane@northernwind.org",
    ], $channel);

    $job = Queue::getJob($channel);

    expect($job->getPayload())->toBe($firstPayload);
    expect($job->getType())->toBe($firstType);
});

it("should get the last added job in default channel for sqlite driver in filo mode", function (): void {
    $lastPayload = [
        "email" => "jane@northerwind.com",
    ];
    $lastType = "password-reset";

    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);
    Queue::addJob($lastType, $lastPayload);

    $job = Queue::getJob();

    expect($job->getPayload())->toBe($lastPayload);
    expect($job->getType())->toBe($lastType);
});

it("should get the last added job in custom channel for sqlite driver in filo mode", function (): void {
    $lastPayload = [
        "email" => "jane@northerwind.com",
    ];
    $lastType = "password-reset";
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ], $channel);
    Queue::addJob($lastType, $lastPayload, $channel);

    $job = Queue::getJob($channel);

    expect($job->getPayload())->toBe($lastPayload);
    expect($job->getType())->toBe($lastType);
});

it("should get the last added job in default channel for file driver in filo mode", function (): void {
    $lastPayload = [
        "email" => "jane@northerwind.com",
    ];
    $lastType = "password-reset";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);
    Queue::addJob($lastType, $lastPayload);

    $job = Queue::getJob();

    expect($job->getPayload())->toBe($lastPayload);
    expect($job->getType())->toBe($lastType);
});

it("should return false if no job have been created using the file driver when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);

    expect(Queue::hasJob())->toBeFalse();
});

it("should return false if no job have been created using the sqlite driver when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);

    expect(Queue::hasJob())->toBeFalse();
});

it("should return false if a job have been created with the file driver in fifo mode on the default channel and then consumed before when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);
    Queue::getJob();

    expect(Queue::hasJob())->toBeFalse();
});

it("should return false if a job have been created with the file driver in fifo mode on a custom channel and then consumed before when calling hasJob", function (): void {
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ], $channel);
    Queue::getJob($channel);

    expect(Queue::hasJob($channel))->toBeFalse();
});

it("should return false if a job have been created with the file driver in filo mode on the default channel and then consumed before when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);
    Queue::getJob();

    expect(Queue::hasJob())->toBeFalse();
});

it("should return false if a job have been created with the file driver in filo mode on a custom channel and then consumed before when calling hasJob", function (): void {
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ], $channel);
    Queue::getJob($channel);

    expect(Queue::hasJob($channel))->toBeFalse();
});

it("should return false if a job have been created with the sqlite driver in fifo mode on the default channel and then consumed before when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);
    Queue::getJob();

    expect(Queue::hasJob())->toBeFalse();
});

it("should return false if a job have been created with the sqlite driver in fifo mode on a custom channel and then consumed before when calling hasJob", function (): void {
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ], $channel);
    Queue::getJob($channel);

    expect(Queue::hasJob($channel))->toBeFalse();
});

it("should return false if a job have been created with the sqlite driver in filo mode on the default channel and then consumed before when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);
    Queue::getJob();

    expect(Queue::hasJob())->toBeFalse();
});

it("should return false if a job have been created with the sqlite driver in filo mode on a custom channel and then consumed before when calling hasJob", function (): void {
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ], $channel);
    Queue::getJob($channel);

    expect(Queue::hasJob($channel))->toBeFalse();
});

it("should return true if a job have been created with the file driver in fifo on the default channel mode before when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);

    expect(Queue::hasJob())->toBeTrue();
});

it("should return true if a job have been created with the file driver in fifo on a custom channel mode before when calling hasJob", function (): void {
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ], $channel);

    expect(Queue::hasJob($channel))->toBeTrue();
});

it("should return true if a job have been created with the file driver in filo on the default channel mode before when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);

    expect(Queue::hasJob())->toBeTrue();
});

it("should return true if a job have been created with the file driver in filo on a custom channel mode before when calling hasJob", function (): void {
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ], $channel);

    expect(Queue::hasJob($channel))->toBeTrue();
});

it("should return true if a job have been created with the sqlite driver in fifo on the default channel mode before when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);

    expect(Queue::hasJob())->toBeTrue();
});

it("should return true if a job have been created with the sqlite driver in fifo on a custom channel mode before when calling hasJob", function (): void {
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ], $channel);

    expect(Queue::hasJob($channel))->toBeTrue();
});

it("should return true if a job have been created with the sqlite driver in filo on the default channel mode before when calling hasJob", function (): void {
    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);

    expect(Queue::hasJob())->toBeTrue();
});

it("should return true if a job have been created with the sqlite driver in filo on a custom channel mode before when calling hasJob", function (): void {
    $channel = "custom";

    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ], $channel);

    expect(Queue::hasJob($channel))->toBeTrue();
});

it("should throw an exception when setting the file driver if the folder does not exist", function (): void {
    $folder = __DIR__ . "/not-found";

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("The file \"$folder\" does not exist.");

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => $folder,
    ]);
});

it("should throw an exception when setting the file driver if the folder is not a string", function (): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("Expected a string. Got: integer");

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => 42,
    ]);
});

it("should throw an exception when setting the file driver if the folder is not present in the parameters", function (): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("Expected the key \"folder\" to exist.");

    Queue::setDriver(QUEUE_DRIVER_FILE, []);
});

it("should throw an exception when trying to add a payload that is not convertible to JSON", function (): void {
    $this->expectException(JsonException::class);

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::addJob("account-created", [
        "file" => fopen(__FILE__, "r"),
    ]);
});

it("should throw an exception if no job file has been created before calling getJob", function (): void {
    $folder = __DIR__ . "/misc/jobs";
    $file = $folder . "/default.job";

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("no job found");

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => $folder,
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::getJob();
});

it("should throw an exception if not job has been found", function (): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("no job found");

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FIFO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);
    Queue::getJob();
    Queue::getJob();
});

it("should throw an exception if not job has been found with filo type", function (): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("no job found");

    Queue::setDriver(QUEUE_DRIVER_FILE, [
        "folder" => __DIR__ . "/misc/jobs",
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);
    Queue::getJob();
    Queue::getJob();
});

it("should throw an exception if not job has been found with sqlite driver", function (): void {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("no job found");

    Queue::setDriver(QUEUE_DRIVER_SQLITE, [
        "pdo" => new PDO("sqlite:" . __DIR__ . "/misc/jobs/database.sqlite"),
        "table" => [
            "name" => "jobs",
            "columns" => [
                "id" => "id",
                "payload" => "payload",
                "type" => "type",
                "channel" => "channel",
            ],
        ],
    ]);
    Queue::setType(QUEUE_TYPE_FILO);
    Queue::addJob("account-created", [
        "email" => "john@doe.com",
    ]);
    Queue::getJob();
    Queue::getJob();
});
