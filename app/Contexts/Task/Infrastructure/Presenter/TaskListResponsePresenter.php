<?php

declare(strict_types=1);

namespace App\Contexts\Task\Infrastructure\Presenter;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\UseCase\Output\ListOutput;

class TaskListResponsePresenter
{
    /**
     * @param ListOutput $output
     * @return array
     */
    public function getResponse(ListOutput $output): array
    {
        return
            collect($output->getTaskList()->toArray())
            ->map(
                fn (Task $task) =>
                [
                    'id' => $task->getId(),
                    'title' => $task->getTitle()->get(),
                    'status' => $task->getStatus()->get()->value,
                ]
            )->values()->toArray();
    }
}
