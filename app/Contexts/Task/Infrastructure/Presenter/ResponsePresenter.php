<?php

declare(strict_types=1);

namespace App\Contexts\Task\Infrastructure\Presenter;

use App\Common\UseCase\Output;

trait ResponsePresenter
{
    /**
     * @param Output $output
     * @return array
     */
    public function getErrorResponse(Output $output): array
    {
        return ['errors' => $output->getErrorMessages()];
    }
}
