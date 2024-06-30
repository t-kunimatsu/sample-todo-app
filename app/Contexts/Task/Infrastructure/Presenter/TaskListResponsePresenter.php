<?php

declare(strict_types=1);

namespace App\Contexts\Task\Infrastructure\Presenter;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\Domain\Enum\Statuses;
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
            collect(Statuses::cases())->mapWithKeys(
                fn (Statuses $status) =>
                [
                    $status->value => collect($output->getTaskList()->toArray())
                        ->filter(fn (Task $task) => $task->status->get() === $status)
                        ->map(
                            fn (Task $task) =>
                            [
                                'id' => $task->getId(),
                                'title' => $task->getTitle()->get(),
                                'status' => $task->getStatus()->get()->value,
                            ]
                        )->values()->toArray(),
                ]
            )->toArray();
    }
}
