<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Persistence;

use App\Contexts\Task\Domain\Entity\Task;
use Illuminate\Support\Collection;

interface TaskRepository
{
    /**
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task;

    /**
     * @param array $ids
     * @return Collection<Task>
     */
    public function getByIds(array $ids): Collection;

    /**
     * @param string $status
     * @return Collection<Task>
     */
    public function getByStatus(string $status): Collection;

    /**
     * @return Collection<Task>
     */
    public function all(): Collection;

    /**
     * @param Task $task
     * @return Task
     */
    public function create(Task $task): Task;

    /**
     * @param Task $task
     * @return void
     */
    public function update(Task $task): void;

    /**
     * @param Task $task
     * @return void
     */
    public function delete(Task $task): void;
}
