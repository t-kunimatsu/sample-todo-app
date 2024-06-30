<?php

declare(strict_types=1);

namespace Tests\Unit\Contexts\Task\UseCase;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\Domain\Entity\TaskList;
use App\Contexts\Task\Domain\Enum\Statuses;
use App\Contexts\Task\Domain\Exception\GapThresholdException;
use App\Contexts\Task\Domain\Factory\TaskFactory;
use App\Contexts\Task\Domain\Factory\TaskListFactory;
use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Contexts\Task\Domain\Service\OrderCalculateService;
use App\Contexts\Task\Domain\Value\Status;
use App\Contexts\Task\Domain\Value\Title;
use App\Contexts\Task\UseCase\Input\UpdateInput;
use App\Contexts\Task\UseCase\Output\UpdateOutput;
use App\Contexts\Task\UseCase\UpdateInteractor;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use ReflectionMethod;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    /**
     * 異常系テスト（更新対象のレコードが存在しないケース）
     */
    public function test_error_case_not_found(): void
    {
        $input = Mockery::mock(UpdateInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task_factory = Mockery::mock(TaskFactory::class);
        $task_list_factory = Mockery::mock(TaskListFactory::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);

        $input->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $task_repository
            ->shouldReceive('findById')
            ->once()
            ->with(100)
            ->andReturn(null);

        $interactor = new UpdateInteractor(
            $task_repository,
            $task_factory,
            $task_list_factory,
            $order_calculate_service,
        );
        $output = $interactor->execute($input);
        $this->assertSame(404, $output->getStatusCode());
        $this->assertSame(['タスクが登録されていません。'], $output->getErrorMessages());
    }

    /**
     * 正常系テスト（並び順の指定がないケース）
     */
    public function test_normal_without_position(): void
    {
        $input = Mockery::mock(UpdateInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task_factory = Mockery::mock(TaskFactory::class);
        $task_list_factory = Mockery::mock(TaskListFactory::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);
        $update_task = Mockery::mock(Task::class);
        $task = Mockery::mock(Task::class);
        $status = Mockery::mock(Status::class);

        $input->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $input->shouldReceive('getTitle')
            ->once()
            ->withNoArgs()
            ->andReturn('update title');

        $input->shouldReceive('getPosition')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $task->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $task->shouldReceive('getStatus')
            ->once()
            ->withNoArgs()
            ->andReturn($status);

        $status->shouldReceive('get')
            ->once()
            ->withNoArgs()
            ->andReturn(Statuses::ToDo);

        $task->shouldReceive('getOrder')
            ->once()
            ->withNoArgs()
            ->andReturn(65535);

        $task_repository->shouldReceive('findById')
            ->once()
            ->with(100)
            ->andReturn($task);

        $task_factory->shouldReceive('make')
            ->once()
            ->with(
                100,
                'update title',
                'todo',
                65535,
            )
            ->andReturn($update_task);

        $task_repository->shouldReceive('update')
            ->once()
            ->with($update_task)
            ->andReturnNull();

        $interactor = new UpdateInteractor(
            $task_repository,
            $task_factory,
            $task_list_factory,
            $order_calculate_service,
        );
        $output = $interactor->execute($input);
        $this->assertSame(200, $output->getStatusCode());
    }

    /**
     * 正常系テスト（並び順の指定があるケース）
     */
    public function test_normal_with_position(): void
    {
        $input = Mockery::mock(UpdateInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task_factory = Mockery::mock(TaskFactory::class);
        $task_list_factory = Mockery::mock(TaskListFactory::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);
        $task = Mockery::mock(Task::class);

        // updateOrderをモック化する
        /** @var UpdateInteractor|MockInterface */
        $interactor = Mockery::mock(UpdateInteractor::class, [
            $task_repository,
            $task_factory,
            $task_list_factory,
            $order_calculate_service,
        ])->shouldAllowMockingProtectedMethods()->makePartial();

        $input->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $task_repository->shouldReceive('findById')
            ->once()
            ->with(100)
            ->andReturn($task);

        $input->shouldReceive('getPosition')
            ->once()
            ->withNoArgs()
            ->andReturn(1);

        $interactor->shouldReceive('updateOrder')
            ->once()
            ->with($input, $task, 1)
            ->andReturn(new UpdateOutput());

        $output = $interactor->execute($input);
        $this->assertSame(200, $output->getStatusCode());
    }

    /**
     * updateOrderの正常系テスト（更新不要なケース）
     */
    public function test_updateOrder_not_need_to_update(): void
    {
        $input = Mockery::mock(UpdateInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task_factory = Mockery::mock(TaskFactory::class);
        $task_list_factory = Mockery::mock(TaskListFactory::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);
        $task = Mockery::mock(Task::class);
        $tasks = Mockery::mock(Collection::class);
        $task_list = Mockery::mock(TaskList::class);

        $position = 0;

        $input->shouldReceive('getStatus')
            ->once()
            ->withNoArgs()
            ->andReturn('doing');

        $task_repository->shouldReceive('getByStatus')
            ->once()
            ->with('doing')
            ->andReturn($tasks);

        $task_list_factory->shouldReceive('make')
            ->once()
            ->with($tasks)
            ->andReturn($task_list);

        $task_list->shouldReceive('findIndex')
            ->once()
            ->with($task)
            ->andReturn($position);

        $interactor = new UpdateInteractor(
            $task_repository,
            $task_factory,
            $task_list_factory,
            $order_calculate_service,
        );
        $method = new ReflectionMethod(UpdateInteractor::class, 'updateOrder');
        $method->setAccessible(true);
        $output = $method->invoke($interactor, $input, $task, $position);
        $this->assertSame(200, $output->getStatusCode());
    }

    /**
     * updateOrderの正常系テスト（タスクリストに含まれていないケース）
     */
    public function test_updateOrder_not_include_task_list(): void
    {
        $input = Mockery::mock(UpdateInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task_factory = Mockery::mock(TaskFactory::class);
        $task_list_factory = Mockery::mock(TaskListFactory::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);
        $task = Mockery::mock(Task::class);
        $update_task = Mockery::mock(Task::class);
        $tasks = Mockery::mock(Collection::class);
        $task_list = Mockery::mock(TaskList::class);
        $title = Mockery::mock(Title::class);

        $position = 2;

        $input->shouldReceive('getStatus')
            ->twice()
            ->withNoArgs()
            ->andReturn('doing');

        $task_repository->shouldReceive('getByStatus')
            ->once()
            ->with('doing')
            ->andReturn($tasks);

        $task_list_factory->shouldReceive('make')
            ->once()
            ->with($tasks)
            ->andReturn($task_list);

        $task_list->shouldReceive('findIndex')
            ->once()
            ->with($task)
            ->andReturnNull();

        $task_list->shouldReceive('count')
            ->once()
            ->withNoArgs()
            ->andReturn(2);

        $order_calculate_service->shouldReceive('getOrder')
            ->once()
            ->with(
                $position,
                $task_list,
            )
            ->andReturn(10000);

        $task->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $task->shouldReceive('getTitle')
            ->once()
            ->withNoArgs()
            ->andReturn($title);

        $title->shouldReceive('get')
            ->once()
            ->withNoArgs()
            ->andReturn('title');

        $task_factory->shouldReceive('make')
            ->once()
            ->with(
                100,
                'title',
                'doing',
                10000,
            )
            ->andReturn($update_task);

        $task_repository->shouldReceive('update')
            ->once()
            ->with($update_task);

        $interactor = new UpdateInteractor(
            $task_repository,
            $task_factory,
            $task_list_factory,
            $order_calculate_service,
        );
        $method = new ReflectionMethod(UpdateInteractor::class, 'updateOrder');
        $method->setAccessible(true);
        $output = $method->invoke($interactor, $input, $task, $position);
        $this->assertSame(200, $output->getStatusCode());
    }

    /**
     * updateOrderの正常系テスト（タスクリストに含まれているケース）
     */
    public function test_updateOrder_include_task_list(): void
    {
        $input = Mockery::mock(UpdateInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task_factory = Mockery::mock(TaskFactory::class);
        $task_list_factory = Mockery::mock(TaskListFactory::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);
        $task = Mockery::mock(Task::class);
        $update_task = Mockery::mock(Task::class);
        $tasks = Mockery::mock(Collection::class);
        $task_list = Mockery::mock(TaskList::class);
        $task_list_without_own = Mockery::mock(TaskList::class);
        $title = Mockery::mock(Title::class);

        $position = 2;

        $input->shouldReceive('getStatus')
            ->twice()
            ->withNoArgs()
            ->andReturn('doing');

        $task_repository->shouldReceive('getByStatus')
            ->once()
            ->with('doing')
            ->andReturn($tasks);

        $task_list_factory->shouldReceive('make')
            ->once()
            ->with($tasks)
            ->andReturn($task_list);

        $task_list->shouldReceive('toArray')
            ->twice()
            ->withNoArgs()
            ->andReturn([]);

        $task_list_factory->shouldReceive('makeFromArray')
            ->once()
            ->withAnyArgs()
            ->andReturn($task_list_without_own);

        $task_list->shouldReceive('findIndex')
            ->once()
            ->with($task)
            ->andReturn(1);

        $task_list_without_own->shouldReceive('count')
            ->once()
            ->withNoArgs()
            ->andReturn(2);

        $order_calculate_service->shouldReceive('getOrder')
            ->once()
            ->with(
                $position,
                $task_list_without_own,
            )
            ->andReturn(10000);

        $task->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $task->shouldReceive('getTitle')
            ->once()
            ->withNoArgs()
            ->andReturn($title);

        $title->shouldReceive('get')
            ->once()
            ->withNoArgs()
            ->andReturn('title');

        $task_factory->shouldReceive('make')
            ->once()
            ->with(
                100,
                'title',
                'doing',
                10000,
            )
            ->andReturn($update_task);

        $task_repository->shouldReceive('update')
            ->once()
            ->with($update_task);

        $interactor = new UpdateInteractor(
            $task_repository,
            $task_factory,
            $task_list_factory,
            $order_calculate_service,
        );
        $method = new ReflectionMethod(UpdateInteractor::class, 'updateOrder');
        $method->setAccessible(true);
        $output = $method->invoke($interactor, $input, $task, $position);
        $this->assertSame(200, $output->getStatusCode());
    }

    /**
     * updateOrderの正常系テスト（リセットが必要になるケース）
     */
    public function test_updateOrder_need_reset(): void
    {
        $input = Mockery::mock(UpdateInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task_factory = Mockery::mock(TaskFactory::class);
        $task_list_factory = Mockery::mock(TaskListFactory::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);
        $task = Mockery::mock(Task::class);
        $tasks = Mockery::mock(Collection::class);
        $task_list = Mockery::mock(TaskList::class);

        // updateOrderをモック化する
        /** @var UpdateInteractor|MockInterface */
        $interactor = Mockery::mock(UpdateInteractor::class, [
            $task_repository,
            $task_factory,
            $task_list_factory,
            $order_calculate_service,
        ])->shouldAllowMockingProtectedMethods()->makePartial();

        $position = 2;

        $input->shouldReceive('getStatus')
            ->once()
            ->withNoArgs()
            ->andReturn('doing');

        $task_repository->shouldReceive('getByStatus')
            ->once()
            ->with('doing')
            ->andReturn($tasks);

        $task_list_factory->shouldReceive('make')
            ->once()
            ->with($tasks)
            ->andReturn($task_list);

        $task_list->shouldReceive('findIndex')
            ->once()
            ->with($task)
            ->andReturnNull();

        $task_list->shouldReceive('count')
            ->once()
            ->withNoArgs()
            ->andReturn(2);

        $exception = new GapThresholdException();
        $order_calculate_service->shouldReceive('getOrder')
            ->once()
            ->with(
                $position,
                $task_list,
            )
            ->andThrow($exception);

        $interactor->shouldReceive('resetAllOrders')
            ->once()
            ->with(
                $input,
                $task_list,
                $task,
                $position,
            )
            ->andReturn(new UpdateOutput());

        $method = new ReflectionMethod(UpdateInteractor::class, 'updateOrder');
        $method->setAccessible(true);
        $output = $method->invoke($interactor, $input, $task, $position);
        $this->assertSame(200, $output->getStatusCode());
    }

    /**
     * resetAllOrdersの正常系テスト
     */
    public function test_resetAllOrders_normal(): void
    {
        $title = Mockery::mock(Title::class);
        $status = Mockery::mock(Status::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);

        // 配列操作をテストする必要があるので、実体を使用
        $task_list_without_own = TaskList::make([
            Task::make(1, $title, $status, 100),
            Task::make(2, $title, $status, 200),
        ]);
        $task = Task::make(3, $title, $status, 300);
        $input = new UpdateInput(3, 'title3', 'doing', 1);

        $title->shouldReceive('get')
            ->times(3)
            ->withNoArgs()
            ->andReturn('title');

        $status->shouldReceive('get')
            ->times(2)
            ->withNoArgs()
            ->andReturn(Statuses::Doing);

        $orders = [65535 * 1, 65535 * 2, 65535 * 3];
        $order_calculate_service->shouldReceive('getOrdersForResetAll')
            ->once()
            ->with(3)
            ->andReturn($orders);

        $task_repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (Task $task) {
                return (
                    $task->getId() === 1 &&
                    $task->getOrder() === 65535 * 1
                );
            }));
        $task_repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (Task $task) {
                return (
                    $task->getId() === 3 &&
                    $task->getOrder() === 65535 * 2
                );
            }));
        $task_repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (Task $task) {
                return (
                    $task->getId() === 2 &&
                    $task->getOrder() === 65535 * 3
                );
            }));

        DB::shouldReceive('beginTransaction')->once()->withNoArgs();
        DB::shouldReceive('commit')->once()->withNoArgs();

        $interactor = new UpdateInteractor(
            $task_repository,
            new TaskFactory(),
            new TaskListFactory(),
            $order_calculate_service,
        );
        $method = new ReflectionMethod(UpdateInteractor::class, 'resetAllOrders');
        $method->setAccessible(true);
        $output = $method->invoke($interactor, $input, $task_list_without_own, $task, 1);
        $this->assertSame(200, $output->getStatusCode());
    }

    /**
     * resetAllOrdersの異常系テスト（DBエラー）
     */
    public function test_resetAllOrders_error(): void
    {
        $title = Mockery::mock(Title::class);
        $status = Mockery::mock(Status::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);

        // 配列操作をテストする必要があるので、実体を使用
        $task_list_without_own = TaskList::make([
            Task::make(1, $title, $status, 100),
            Task::make(2, $title, $status, 200),
        ]);
        $task = Task::make(3, $title, $status, 300);
        $input = new UpdateInput(3, 'title3', 'doing', 1);

        $title->shouldReceive('get')
            ->times(2)
            ->withNoArgs()
            ->andReturn('title');

        $status->shouldReceive('get')
            ->once()
            ->withNoArgs()
            ->andReturn(Statuses::Doing);

        $orders = [65535 * 1, 65535 * 2, 65535 * 3];
        $order_calculate_service->shouldReceive('getOrdersForResetAll')
            ->once()
            ->with(3)
            ->andReturn($orders);

        $task_repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (Task $task) {
                return (
                    $task->getId() === 1 &&
                    $task->getOrder() === 65535 * 1
                );
            }));

        $exception = new Exception('DBエラー');
        $task_repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (Task $task) {
                return (
                    $task->getId() === 3 &&
                    $task->getOrder() === 65535 * 2
                );
            }))
            ->andThrow($exception);

        DB::shouldReceive('beginTransaction')->once()->withNoArgs();
        DB::shouldReceive('rollback')->once()->withNoArgs();

        $interactor = new UpdateInteractor(
            $task_repository,
            new TaskFactory(),
            new TaskListFactory(),
            $order_calculate_service,
        );
        $method = new ReflectionMethod(UpdateInteractor::class, 'resetAllOrders');
        $method->setAccessible(true);
        $output = $method->invoke($interactor, $input, $task_list_without_own, $task, 1);
        $this->assertSame(500, $output->getStatusCode());
        $this->assertSame(['タスクの並び順の更新に失敗しました。'], $output->getErrorMessages());
    }
}
