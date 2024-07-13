<?php

declare(strict_types=1);

namespace App\Contexts\Task\UseCase;

use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Contexts\Task\UseCase\Input\DeleteInput;
use App\Contexts\Task\UseCase\Output\DeleteOutput;
use Symfony\Component\HttpFoundation\Response;

class DeleteInteractor
{
    /**
     * @param TaskRepository $task_repository
     */
    public function __construct(
        private readonly TaskRepository $task_repository,
    ) {
    }

    /**
     * @param DeleteInput $input
     * @return DeleteOutput
     */
    public function execute(DeleteInput $input): DeleteOutput
    {
        $task = $this->task_repository->findById($input->getId());
        if ($task === null) {
            return new DeleteOutput(
                errors: ['タスクが登録されていません。'],
                status_code: Response::HTTP_NOT_FOUND
            );
        }
        $this->task_repository->delete($task);
        return new DeleteOutput();
    }
}
