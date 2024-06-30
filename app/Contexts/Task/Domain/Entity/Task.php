<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Entity;

use App\Contexts\Task\Domain\Value\Order;
use App\Contexts\Task\Domain\Value\Status;
use App\Contexts\Task\Domain\Value\Title;

class Task
{
    /**
     * @param int|null $id
     * @param Title $title
     * @param Status $status
     * @param int $order
     */
    private function __construct(
        readonly ?int $id,
        readonly Title $title,
        readonly Status $status,
        readonly int $order,
    ) {
    }

    /**
     * @param int $id
     * @param Title $title
     * @param Status $status
     * @param int $order
     * @return self
     */
    public static function make(
        int $id,
        Title $title,
        Status $status,
        int $order,
    ): self {
        return new self(
            id: $id,
            title: $title,
            status: $status,
            order: $order,
        );
    }

    /**
     * @param Title $title
     * @param Status $status
     * @param int $order
     * @return self
     */
    public static function makeNew(
        Title $title,
        Status $status,
        int $order,
    ): self {
        return new self(
            id: null,
            title: $title,
            status: $status,
            order: $order,
        );
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Title
     */
    public function getTitle(): Title
    {
        return $this->title;
    }

    /**
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }
}
