<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Exception;

use Exception;

class GapThresholdException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(
        $message = "The gap between orders is below the acceptable threshold.",
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
