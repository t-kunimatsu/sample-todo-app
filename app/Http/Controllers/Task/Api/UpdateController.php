<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task\Api;

use App\Contexts\Task\Infrastructure\Presenter\TaskUpdateResponsePresenter;
use App\Contexts\Task\UseCase\Input\UpdateInput;
use App\Contexts\Task\UseCase\UpdateInteractor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class UpdateController extends Controller
{
    /**
     * @param int $id
     * @param UpdateRequest $request
     * @param UpdateInteractor $interactor
     * @param TaskUpdateResponsePresenter $presenter
     * @return JsonResponse
     */
    public function __invoke(
        int $id,
        UpdateRequest $request,
        UpdateInteractor $interactor,
        TaskUpdateResponsePresenter $presenter,
    ): JsonResponse {
        $input = new UpdateInput(
            $id,
            $request->input('title'),
            $request->input('status'),
            $request->input('position'),
        );
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
