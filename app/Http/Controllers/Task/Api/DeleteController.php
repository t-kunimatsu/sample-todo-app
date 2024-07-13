<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task\Api;

use App\Contexts\Task\Infrastructure\Presenter\TaskDeleteResponsePresenter;
use App\Contexts\Task\UseCase\Input\DeleteInput;
use App\Contexts\Task\UseCase\DeleteInteractor;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class DeleteController extends Controller
{
    /**
     * @param int $id
     * @param DeleteInteractor $interactor
     * @param TaskDeleteResponsePresenter $presenter
     * @return JsonResponse
     */
    public function __invoke(
        int $id,
        DeleteInteractor $interactor,
        TaskDeleteResponsePresenter $presenter,
    ): JsonResponse {
        $input = new DeleteInput($id);
        $output = $interactor->execute($input);
        if ($output->isError()) {
            return Response::json(
                $presenter->getErrorResponse($output),
                $output->getStatusCode(),
            );
        }
        return Response::json(
            $output->getStatusCode(),
        );
    }
}
