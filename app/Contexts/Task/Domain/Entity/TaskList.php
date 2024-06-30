<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Entity;

class TaskList
{
    /**
     * @param array $task_list
     */
    private function __construct(private readonly array $task_list)
    {
    }

    /**
     * @param array $task_list
     * @return self
     */
    public static function make(array $task_list): self
    {
        return new self($task_list);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->task_list;
    }

    /**
     * @param int $index
     * @return Task|null
     */
    public function getTask(int $index): ?Task
    {
        if ($index < 0 || $index >= count($this->task_list)) {
            return null;
        }
        return $this->task_list[$index];
    }

    /**
     * @param Task $target
     * @return int|null
     */
    public function findIndex(Task $target): ?int
    {
        foreach ($this->task_list as $index => $task) {
            if ($task->getId() === $target->getId()) {
                return $index;
            }
        }
        return null;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->task_list);
    }
}
