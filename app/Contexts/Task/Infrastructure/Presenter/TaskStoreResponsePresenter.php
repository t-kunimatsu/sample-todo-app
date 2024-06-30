<?php

declare(strict_types=1);

namespace App\Contexts\Task\Infrastructure\Presenter;

use App\Contexts\Task\UseCase\Output\StoreOutput;

class TaskStoreResponsePresenter
{
    /**
     * @param StoreOutput $output
     * @return array
     */
    public function getResponse(StoreOutput $output): array
    {
        $task = $output->getTask();
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle()->get(),
            'status' => $task->getStatus()->get()->value,
        ];
    }
}
