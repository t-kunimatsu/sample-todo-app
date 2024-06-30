<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Factory;

use App\Contexts\Task\Domain\Entity\TaskList;
use Illuminate\Support\Collection;

class TaskListFactory
{
    /**
     * @param Collection<Task> $taskList
     * @return TaskList
     */
    public function make(Collection $taskList): TaskList
    {
        return TaskList::make($taskList->toArray());
    }

    /**
     * @param array $task_array
     * @return TaskList
     */
    public function makeFromArray(array $task_array): TaskList
    {
        return TaskList::make($task_array);
    }
}
