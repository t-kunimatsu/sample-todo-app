<?php

declare(strict_types=1);

namespace App\Contexts\Task\UseCase\Input;


class StoreInput
{
    /**
     * @param string $title
     * @param string $status
     */
    public function __construct(
        private readonly string $title,
        private readonly string $status,
    ) {
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
