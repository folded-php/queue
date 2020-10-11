# folded/history

Queue job management for your web app.

## Summary

- [About](#about)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Examples](#examples)
- [Version support](#version-support)

## About

I created this library to be able to easily create jobs to enqueue and retrieve in a later time. I use it for tasks that I don't want to block on my PHP requests (like sending an email after an account has been created, which I can send later).

Folded is a constellation of packages to help you setting up a web app easily, using ready to plug in packages.

- [folded/action](https://github.com/folded-php/action): A way to organize your controllers for your web app.
- [folded/config](https://github.com/folded-php/config): Configuration utilities for your PHP web app.
- [folded/crypt](https://github.com/folded-php/crypt): Encrypt and decrypt strings for your web app.
- [folded/exception](https://github.com/folded-php/exception): Various kind of exception to throw for your web app.
- [folded/file](https://github.com/folded-php/file): Manipulate files with functions for your web app.
- [folded/history](https://github.com/folded-php/history): Manipulate the browser history for your web app.
- [folded/http](https://github.com/folded-php/http): HTTP utilities for your web app.
- [folded/orm](https://github.com/folded-php/orm): An ORM for you web app.
- [folded/routing](https://github.com/folded-php/routing): Routing functions for your PHP web app.
- [folded/request](https://github.com/folded-php/request): Request utilities, including a request validator, for your PHP web app.
- [folded/session](https://github.com/folded-php/session): Session functions for your web app.
- [folded/view](https://github.com/folded-php/view): View utilities for your PHP web app.

## Features

- Can add jobs to a queue
- Can retrieve jobs from a queue
- Can check if there is jobs to process in a queue
- Can set the queue driver
  - file
- Can set the type of job retreival
  - FIFO: First In, First Out
  - FILO: First In, Last Out

## Requirements

- PHP version >= 7.4.0
- Composer installed

## Installation

- [1. Install the package](#1-install-the-package)
- [2. Add the bootstrap code](#2-add-the-bootstrap-code)

### 1. Install the package

In your root folder, run this command:

```bash
composer required folded/queue
```

### 2. Add the bootstrap code

As early as possible, and by making sure the code is ran before add or get jobs in a queue:

```php
use function Folded\setQueueDriver;
use function Folded\setQueueType;

setQueueDriver("file", [
  "folder" =>
]);

setQueueType("fifo");
```

Check the [examples](#examples) for a complete list of drivers and type of queue, as well as constants to avoid writting the queue drivers and type by hand.

## Examples

- [1. Add a job to a queue](#1-add-a-job-to-a-queue)
- [2. Get a job from a queue](#2-get-a-job-from-a-queue)
- [3. Check if there is a job to process from a queue](#3-check-if-there-is-a-job-to-process-from-a-queue)
- [4. Set the queue driver](#4-set-the-queue-driver)
- [5. Set the queue type](#5-set-the-queue-type)

### 1. Add a job to a queue

In this example, we will add a job to a queue.

```php
use function Folded\addJobToQueue;

addJobToQueue("account-created", [
  "email" => "john@doe.com",
]);
```

If you used the "file" driver, you will see a new `default.job` file containing your job.

If you want to organize in multiple queue identified by a special name, use the third parameter:

```php
use function Folded\addJobToQueue;

addJobToQueue("account-created", [
  "email" => "john@doe.com",
], "emails");
```

If you use the "file" driver, you will see a new `emails.job` file named after the name of your queue, and containing your job.

### 2. Get a job from a queue

In this example, we will get the first job in our queue.

```php
use function Folded\getJobFromQueue;

$job = getJobFromQueue();

echo "job type is {$job->getType()}";

var_dump($job->getPayload());
```

As you can see, you will get an instance of `Folded\QueueJob` when getting the job quuee.

By default, you retreive jobs from the "default" queue. If you use the "file" driver, the job is taken from the "default.job" queue file.

When a job is taken, **it is removed from the queue**.

If you need to get a job from a named queue, use the first parameter:

```php
use function Folded\getJobFromQueue;

$job = getJobFromQueue("emails");

echo "job type is {$job->getType()}";

var_dump($job->getPayload());
```

For instance, if you use the "file" driver, this will take the job from the "emails.job" queue file.

### 3. Check if there is a job to process from a queue

In this example, we will check if there is a job to process from a queue.

```php
use function Folded\hasJobFromQueue;

if (hasJobFromQueue()) {
  // can get the job queue
}
```

By default, this method will check if a job is available from the default queue. if you need to check from a named queue, use the first parameter:

```php
use function Folded\hasJobFromQueue;

if (hasJobFromQueue("emails")) {
  // can get the job queue
}
```

### 4. Set the queue driver

In this example, we will set the queue driver.

```php
use function Folded\setQueueDriver;

setQueueDriver("file");
```

Here is a list of supported queue drivers:

- file

If you do not want to write the queue driver by hand, you can use constants instead:

```php
use function Folded\setQueueDriver;
use const QUEUE_DRIVER_FILE;

setQueueDriver(QUEUE_DRIVER_FILE);
```

Here is a list of available constants:

- `QUEUE_DRIVER_FILE`

### 5. Set the queue type

In this example, we will set the kind of job retreival.

```php
use function Folded\setQueueType;

setQueueType("fifo");
```

Here is a list of supported queue types:

- `fifo`: First In, First Out
- `filo`: First In, Last Out

If you do not want to write the queue type by hand, you can use constants instead:

- `QUEUE_TYPE_FIFO`
- `QUEUE_TYPE_FILO`

So the previous code snippet becomes:

```php
use function Folded\setQueueType;
use const Folded\QUEUE_TYPE_FIFO;

setQueueType(QUEUE_TYPE_FIFO);
```

## Version support

|        | 7.3 | 7.4 | 8.0 |
| ------ | --- | --- | --- |
| v0.1.0 | ❌  | ✔️  | ❓  |
