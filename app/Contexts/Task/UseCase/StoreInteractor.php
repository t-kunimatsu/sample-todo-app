<?php

declare(strict_types=1);

namespace App\Contexts\Task\UseCase;

use App\Contexts\Task\Domain\Factory\TaskFactory;
use App\Contexts\Task\Domain\Factory\TaskListFactory;
use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Contexts\Task\Domain\Service\OrderCalculateService;
use App\Contexts\Task\UseCase\Input\StoreInput;
use App\Contexts\Task\UseCase\Output\StoreOutput;

class StoreInteractor
{
    /**
     * @param TaskRepository $task_repository
     * @param TaskFactory $task_factory
     * @param TaskListFactory $task_list_factory
     * @param OrderCalculateService $order_calculate_service
     */
    public function __construct(
        private readonly TaskRepository $task_repository,
        private readonly TaskFactory $task_factory,
        private readonly TaskListFactory $task_list_factory,
        private readonly OrderCalculateService $order_calculate_service,
    ) {
    }

    /**
     * @param StoreInput $input
     * @return StoreOutput
     */
    public function execute(StoreInput $input): StoreOutput
    {
        // ステータス内の既存タスクのorderからorderを計算する（タスクを追加する位置は末尾固定）
        $tasks = $this->task_repository->getByStatus($input->getStatus());
        $task_list = $this->task_list_factory->make($tasks);
        $order = $this->order_calculate_service->getOrder($tasks->count(), $task_list);

        $new_task = $this->task_factory->makeNew(
            title: $input->getTitle(),
            status: $input->getStatus(),
            order: $order,
        );
        $task = $this->task_repository->create($new_task);
        return new StoreOutput(
            task: $task,
        );
    }
}
