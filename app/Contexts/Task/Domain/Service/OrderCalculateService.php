<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Service;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\Domain\Entity\TaskList;
use App\Contexts\Task\Domain\Exception\GapThresholdException;

/**
 * GAP戦略によりorderを計算するためのサービス。
 */
class OrderCalculateService
{
    private const GAP = 65535;
    private const GAP_MIN = 10; // ギャップがこの値以下になったらリセットする

    /**
     * 前後のTaskが持つorderからorderを計算し、Orderを生成する。
     * 
     * @param int $position
     * @param TaskList $task_list　移動対象タスクを除いた、移動先のタスクリスト
     * @return int
     * @throws GapThresholdException
     */
    public function getOrder(
        int $position,
        TaskList $task_list,
    ): int {
        $count = $task_list->count();
        // 移動先が空の場合
        if ($count === 0) {
            return self::GAP;
        }
        // 移動先の末尾に追加する場合
        if ($position >= $count) {
            return $task_list->getTask($position - 1)->getOrder() + self::GAP;
        }
        // 割り込みの場合、前後のorderを元に計算する
        $previous_task = $task_list->getTask($position - 1);
        $next_task = $task_list->getTask($position);
        // 割り込む隙間に十分な余裕があるかチェックする
        $previous_task = $task_list->getTask($position - 1);
        $next_task = $task_list->getTask($position);
        $this->check($previous_task, $next_task);
        // 移動先の先頭に移動する場合（割り込み）
        if ($previous_task === null) {
            return intdiv($next_task->getOrder(), 2);
        }
        // 上記以外に移動する場合（割り込み）
        return intdiv($previous_task->getOrder() + $next_task->getOrder(), 2);
    }

    /**
     * orderを振り直す。
     * 
     * @param int $count
     * @return array<int>
     */
    public function getOrdersForResetAll(int $count): array
    {
        return array_map(fn ($position) => self::GAP * $position, range(1, $count));
    }

    /**
     * 前後のTaskが持つOrderの差がGAPの閾値を下回っていないかチェックする。
     * 
     * @param Task|null $previous_task
     * @param Task|null $next_task
     * @return void
     * @throws GapThresholdException
     */
    public function check(?Task $previous_task, ?Task $next_task): void
    {
        if ($next_task === null) {
            return;
        }
        if (
            $previous_task === null &&
            $next_task->getOrder() > self::GAP_MIN
        ) {
            return;
        }
        if (
            $previous_task !== null &&
            $next_task->getOrder() - $previous_task->getOrder() > self::GAP_MIN
        ) {
            return;
        }
        throw new GapThresholdException();
    }
}
