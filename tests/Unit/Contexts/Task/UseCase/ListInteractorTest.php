<?php

declare(strict_types=1);

namespace Tests\Unit\Contexts\Task\UseCase;

use App\Contexts\Task\Domain\Entity\TaskList;
use App\Contexts\Task\Domain\Factory\TaskListFactory;
use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Contexts\Task\UseCase\Input\ListInput;
use App\Contexts\Task\UseCase\ListInteractor;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    /**
     * 正常系テスト
     */
    public function test_normal_case(): void
    {
        $input = Mockery::mock(ListInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task_list_factory = Mockery::mock(TaskListFactory::class);
        $tasks = Mockery::mock(Collection::class);
        $task_list = Mockery::mock(TaskList::class);

        $task_repository
            ->shouldReceive('all')
            ->once()
            ->withNoArgs()
            ->andReturn($tasks);

        $task_list_factory
            ->shouldReceive('make')
            ->once()
            ->with($tasks)
            ->andReturn($task_list);

        $interactor = new ListInteractor(
            $task_repository,
            $task_list_factory,
        );
        $output = $interactor->execute($input);
        $this->assertSame($task_list, $output->getTaskList());
    }
}
