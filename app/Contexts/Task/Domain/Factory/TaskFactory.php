<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Factory;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\Domain\Value\Status;
use App\Contexts\Task\Domain\Value\Title;

class TaskFactory
{
    /**
     * @param int $id
     * @param string $title
     * @param string $status
     * @param int $order
     * @return Task
     */
    public function make(
        int $id,
        string $title,
        string $status,
        int $order,
    ): Task {
        return Task::make(
            id: $id,
            title: Title::make($title),
            status: Status::make($status),
            order: $order,
        );
    }

    /**
     * @param string $title
     * @param string $status
     * @param int $order
     * @return Task
     */
    public function makeNew(
        string $title,
        string $status,
        int $order,
    ): Task {
        return Task::makeNew(
            Title::make($title),
            status: Status::make($status),
            order: $order,
        );
    }
}
