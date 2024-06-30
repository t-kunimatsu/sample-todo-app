<?php

declare(strict_types=1);

namespace App\Common\UseCase;

class Output
{
    /**
     * @param array $errors
     * @param int $status_code
     */
    public function __construct(
        private readonly array $errors = [],
        private readonly int $status_code = 200
    ) {
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status_code;
    }
}
