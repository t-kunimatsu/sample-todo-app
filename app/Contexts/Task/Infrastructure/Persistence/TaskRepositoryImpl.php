<?php

declare(strict_types=1);

namespace App\Contexts\Task\Infrastructure\Persistence;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\Domain\Factory\TaskFactory;
use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Models\Task as TaskModel;
use Illuminate\Support\Collection;

class TaskRepositoryImpl implements TaskRepository
{
    /**
     * @param TaskFactory $task_factory
     */
    public function __construct(
        private readonly TaskFactory $task_factory,
    ) {
    }

    /**
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task
    {
        $model = TaskModel::find($id);
        return $this->convertToTask($model);
    }

    /**
     * @param array $ids
     * @return Collection<Task>
     */
    public function getByIds(array $ids): Collection
    {
        return TaskModel::query()->whereIn('id', $ids)->get()->map(
            fn ($model) => $this->convertToTask($model)
        );
    }

    /**
     * @param string $status
     * @return Collection<Task>
     */
    public function getByStatus(string $status): Collection
    {
        return TaskModel::query()->where('status', $status)->orderBy('order')->get()->map(
            fn ($model) => $this->convertToTask($model)
        );
    }

    /**
     * @return Collection<Task>
     */
    public function all(): Collection
    {
        return TaskModel::query()->orderBy('order')->get()->map(
            fn ($model) => $this->convertToTask($model)
        );
    }

    /**
     * @param Task $task
     * @return Task
     */
    public function create(Task $task): Task
    {
        $model = TaskModel::query()->create([
            'title' => $task->getTitle()->get(),
            'status' => $task->getStatus()->get()->value,
            'order' => $task->getOrder(),
        ]);
        return $this->convertToTask($model);
    }

    /**
     * @param Task $task
     * @return void
     */
    public function update(Task $task): void
    {
        TaskModel::query()
            ->where('id', $task->getId())
            ->update([
                'title' => $task->getTitle()->get(),
                'status' => $task->getStatus()->get()->value,
                'order' => $task->getOrder(),
            ]);
    }

    /**
     * @param Task $task
     * @return void
     */
    public function delete(Task $task): void
    {
        TaskModel::query()
            ->where('id', $task->getId())
            ->delete();
    }

    /**
     * @param TaskModel|null $model
     * @return Task|null
     */
    private function convertToTask(?TaskModel $model): ?Task
    {
        if ($model === null) {
            return null;
        }
        return $this->task_factory->make(
            $model->id,
            $model->title,
            $model->status,
            $model->order,
        );
    }
}
