<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task\Api;

use App\Contexts\Task\Infrastructure\Presenter\TaskStoreResponsePresenter;
use App\Contexts\Task\UseCase\Input\StoreInput;
use App\Contexts\Task\UseCase\StoreInteractor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class StoreController extends Controller
{
    /**
     * @param StoreRequest $request
     * @param StoreInteractor $interactor
     * @param TaskStoreResponsePresenter $presenter
     * @return JsonResponse
     */
    public function __invoke(
        StoreRequest $request,
        StoreInteractor $interactor,
        TaskStoreResponsePresenter $presenter,
    ): JsonResponse {
        $input = new StoreInput(
            $request->input('title'),
            $request->input('status'),
        );
        $output = $interactor->execute($input);
        return Response::json(
            $presenter->getResponse($output),
            $output->getStatusCode(),
        );
    }
}
