<?php

declare(strict_types=1);

namespace Tests\Unit\Contexts\Task\UseCase;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\Domain\Entity\TaskList;
use App\Contexts\Task\Domain\Factory\OrderFactory;
use App\Contexts\Task\Domain\Factory\TaskFactory;
use App\Contexts\Task\Domain\Factory\TaskListFactory;
use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Contexts\Task\Domain\Service\OrderCalculateService;
use App\Contexts\Task\UseCase\Input\StoreInput;
use App\Contexts\Task\UseCase\StoreInteractor;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class StoreInteractorTest extends TestCase
{
    /**
     * 正常系テスト
     */
    public function test_normal_case(): void
    {
        $input = Mockery::mock(StoreInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task_factory = Mockery::mock(TaskFactory::class);
        $task_list_factory = Mockery::mock(TaskListFactory::class);
        $order_calculate_service = Mockery::mock(OrderCalculateService::class);
        $tasks = Mockery::mock(Collection::class);
        $task_list = Mockery::mock(TaskList::class);
        $new_task = Mockery::mock(Task::class);
        $task = Mockery::mock(Task::class);

        $input->shouldReceive('getTitle')
            ->once()
            ->withNoArgs()
            ->andReturn('title');

        $input->shouldReceive('getStatus')
            ->twice()
            ->withNoArgs()
            ->andReturn('todo');

        $task_repository
            ->shouldReceive('getByStatus')
            ->once()
            ->with('todo')
            ->andReturn($tasks);

        $task_list_factory->shouldReceive('make')
            ->once()
            ->with($tasks)
            ->andReturn($task_list);

        $tasks->shouldReceive('count')
            ->once()
            ->withNoArgs()
            ->andReturn(10);

        $order_calculate_service->shouldReceive('getOrder')
            ->once()
            ->with(10, $task_list)
            ->andReturn(65535);

        $task_factory
            ->shouldReceive('makeNew')
            ->once()
            ->with(
                'title',
                'todo',
                65535,
            )
            ->andReturn($new_task);

        $task_repository
            ->shouldReceive('create')
            ->once()
            ->with($new_task)
            ->andReturn($task);

        $interactor = new StoreInteractor(
            $task_repository,
            $task_factory,
            $task_list_factory,
            $order_calculate_service,
        );
        $output = $interactor->execute($input);
        $this->assertSame($task, $output->getTask());
    }
}
