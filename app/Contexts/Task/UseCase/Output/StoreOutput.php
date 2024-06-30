<?php

declare(strict_types=1);

namespace App\Contexts\Task\UseCase\Output;

use App\Common\UseCase\Output;
use App\Contexts\Task\Domain\Entity\Task;

class StoreOutput extends Output
{
    /**
     * @param array $errors
     * @param int $status_code
     * @param Task|null $task
     */
    public function __construct(
        array $errors = [],
        int $status_code = 200,
        private ?Task $task = null,
    ) {
        parent::__construct($errors, $status_code);
    }

    /**
     * @return Task|null
     */
    public function getTask(): ?Task
    {
        return  $this->task;
    }
}
