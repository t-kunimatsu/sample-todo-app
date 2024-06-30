<?php

declare(strict_types=1);

namespace App\Contexts\Task\UseCase\Output;

use App\Common\UseCase\Output;
use App\Contexts\Task\Domain\Entity\TaskList;

class ListOutput extends Output
{
    /**
     * @param array $errors
     * @param int $status_code
     * @param TaskList|null $task_list
     */
    public function __construct(
        array $errors = [],
        int $status_code = 200,
        private ?TaskList $task_list = null,
    ) {
        parent::__construct($errors, $status_code);
    }

    /**
     * @return TaskList|null
     */
    public function getTaskList(): ?TaskList
    {
        return  $this->task_list;
    }
}
