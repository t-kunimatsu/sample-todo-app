<?php

declare(strict_types=1);

namespace App\Contexts\Task\UseCase;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\Domain\Entity\TaskList;
use App\Contexts\Task\Domain\Exception\GapThresholdException;
use App\Contexts\Task\Domain\Factory\TaskFactory;
use App\Contexts\Task\Domain\Factory\TaskListFactory;
use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Contexts\Task\Domain\Service\OrderCalculateService;
use App\Contexts\Task\UseCase\Input\UpdateInput;
use App\Contexts\Task\UseCase\Output\UpdateOutput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UpdateInteractor
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
     * @param UpdateInput $input
     * @return UpdateOutput
     */
    public function execute(UpdateInput $input): UpdateOutput
    {
        $task = $this->task_repository->findById($input->getId());
        if ($task === null) {
            return new UpdateOutput(
                errors: ['タスクが登録されていません。'],
                status_code: Response::HTTP_NOT_FOUND
            );
        }
        // 並び順の指定がない場合
        $position = $input->getPosition();
        if ($position === null) {
            $update_task = $this->task_factory->make(
                id: $task->getId(),
                title: $input->getTitle(),
                status: $task->getStatus()->get()->value,
                order: $task->getOrder(),
            );
            $this->task_repository->update($update_task);
            return new UpdateOutput();
        }
        // 並び順の指定がある場合
        return $this->updateOrder($input, $task, $position);
    }

    /**
     * @param UpdateInput $input
     * @param Task $task
     * @param int $position
     * @return UpdateOutput
     */
    protected function updateOrder(UpdateInput $input, Task $task, int $position): UpdateOutput
    {
        $tasks = $this->task_repository->getByStatus($input->getStatus());
        $task_list = $this->task_list_factory->make($tasks);
        // タスクリストに自分自身が含まれているか調べる
        $index_own = $task_list->findIndex($task);
        // タスクリストに含まれていて、かつポジションが同じ場合、orderは変更不要なので何もせずに終了
        if ($index_own === $position) {
            return new UpdateOutput();
        }
        // タスクリストに含まれている場合、除外する
        $task_list_without_own = $this->getTaskListWithoutOwn($task_list, $index_own);
        // 指定の並び順がリストサイズを超えている場合、補正する（末尾にすればよいのでエラーにはしない）
        $count_without_own = $task_list_without_own->count();
        $position = $position <= $count_without_own ? $position : $count_without_own - 1;
        // 挿入位置の前後のorderからorderを計算する
        // （並び順の変更がある場合、タイトルの変更はない。ステータスは変更されるケースもある）
        try {
            $order = $this->order_calculate_service->getOrder($position, $task_list_without_own);
            $update_task = $this->task_factory->make(
                id: $task->getId(),
                title: $task->getTitle()->get(),
                status: $input->getStatus(),
                order: $order,
            );
            $this->task_repository->update($update_task);
            return new UpdateOutput();
        } catch (GapThresholdException $e) {
            // GAPに余裕がない場合、リセットする（同一ステータスの全レコードのorderを振り直す）
            return $this->resetAllOrders(
                $input,
                $task_list_without_own,
                $task,
                $position,
            );
        }
    }

    /**
     * @param UpdateInput $input
     * @param TaskList $task_list_without_own
     * @param Task $task
     * @param int $position
     * @return UpdateOutput
     */
    protected function resetAllOrders(
        UpdateInput $input,
        TaskList $task_list_without_own,
        Task $task,
        int $position,
    ): UpdateOutput {
        // 更新対象のタスクをタスクリストに挿入
        $update_task = $this->task_factory->make(
            id: $task->getId(),
            title: $task->getTitle()->get(),
            status: $input->getStatus(),
            order: $task->getOrder(),
        );
        $new_task_list = $this->getTaskListWithOwn($task_list_without_own, $update_task, $position);
        // リセット後のorderを取得
        $orders = $this->order_calculate_service->getOrdersForResetAll($new_task_list->count());
        DB::beginTransaction();
        try {
            foreach ($new_task_list->toArray() as $index => $task) {
                $update_task = $this->task_factory->make(
                    id: $task->getId(),
                    title: $task->getTitle()->get(),
                    status: $task->getStatus()->get()->value,
                    order: $orders[$index],
                );
                $this->task_repository->update($update_task);
            }
            DB::commit();
            return new UpdateOutput();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            return new UpdateOutput(
                errors: ['タスクの並び順の更新に失敗しました。'],
                status_code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param TaskList $task_list
     * @param int|null $index_own
     * @return TaskList
     */
    private function getTaskListWithoutOwn(TaskList $task_list, ?int $index_own): TaskList
    {
        if ($index_own === null) {
            return $task_list;
        }
        return $this->task_list_factory->makeFromArray(
            array_merge(
                array_slice($task_list->toArray(), 0, $index_own),
                array_slice($task_list->toArray(), $index_own + 1),
            ),
        );
    }

    /**
     * @param TaskList $task_list
     * @param Task $task
     * @param int $position
     * @return TaskList
     */
    private function getTaskListWithOwn(TaskList $task_list, Task $task, int $position): TaskList
    {
        return $this->task_list_factory->makeFromArray(
            array_merge(
                array_slice($task_list->toArray(), 0, $position),
                [$task],
                array_slice($task_list->toArray(), $position),
            ),
        );
    }
}
