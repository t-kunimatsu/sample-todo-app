<?php

declare(strict_types=1);

namespace App\Contexts\Task\UseCase\Input;


class UpdateInput
{
    /**
     * @param int $id
     * @param string $title
     * @param string $status
     * @param int|null $position
     */
    public function __construct(
        private readonly int $id,
        private readonly string $title,
        private readonly string $status,
        private readonly ?int $position,
    ) {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }
}
