<?php

declare(strict_types=1);

namespace Tests\Unit\Contexts\Task\UseCase;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Contexts\Task\UseCase\Input\DeleteInput;
use App\Contexts\Task\UseCase\DeleteInteractor;
use Mockery;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    /**
     * 異常系テスト（更新対象のレコードが存在しないケース）
     */
    public function test_error_case_not_found(): void
    {
        $input = Mockery::mock(DeleteInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);

        $input->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $task_repository
            ->shouldReceive('findById')
            ->once()
            ->with(100)
            ->andReturn(null);

        $interactor = new DeleteInteractor(
            $task_repository,
        );
        $output = $interactor->execute($input);
        $this->assertSame(404, $output->getStatusCode());
        $this->assertSame(['タスクが登録されていません。'], $output->getErrorMessages());
    }

    /**
     * 正常系テスト
     */
    public function test_normal(): void
    {
        $input = Mockery::mock(DeleteInput::class);
        $task_repository = Mockery::mock(TaskRepository::class);
        $task = Mockery::mock(Task::class);

        $input->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $task_repository->shouldReceive('findById')
            ->once()
            ->with(100)
            ->andReturn($task);

        $task_repository->shouldReceive('delete')
            ->once()
            ->with($task)
            ->andReturnNull();

        $interactor = new DeleteInteractor(
            $task_repository,
        );
        $output = $interactor->execute($input);
        $this->assertSame(200, $output->getStatusCode());
    }
}
