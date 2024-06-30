<?php

declare(strict_types=1);

namespace App\Contexts\Task\UseCase;

use App\Contexts\Task\Domain\Factory\TaskListFactory;
use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Contexts\Task\UseCase\Input\ListInput;
use App\Contexts\Task\UseCase\Output\ListOutput;

class ListInteractor
{
    /**
     * @param TaskRepository $task_repository
     * @param TaskListFactory $task_list_factory
     */
    public function __construct(
        private readonly TaskRepository $task_repository,
        private readonly TaskListFactory $task_list_factory,
    ) {
    }

    /**
     * @param ListInput $input
     * @return ListOutput
     */
    public function execute(ListInput $input): ListOutput
    {
        $tasks = $this->task_repository->all();
        $task_list = $this->task_list_factory->make($tasks);
        return new ListOutput(
            task_list: $task_list,
        );
    }
}
