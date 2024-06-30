<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task\Api;

use App\Contexts\Task\Infrastructure\Presenter\TaskListResponsePresenter;
use App\Contexts\Task\UseCase\Input\ListInput;
use App\Contexts\Task\UseCase\ListInteractor;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class ListController extends Controller
{
    /**
     * @param ListInteractor $interactor
     * @param TaskListResponsePresenter $presenter
     * @return JsonResponse
     */
    public function __invoke(
        ListInteractor $interactor,
        TaskListResponsePresenter $presenter,
    ): JsonResponse {
        $input = new ListInput();
        $output = $interactor->execute($input);
        return Response::json(
            $presenter->getResponse($output),
            $output->getStatusCode(),
        );
    }
}
