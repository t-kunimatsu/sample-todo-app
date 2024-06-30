<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Enum;

enum Statuses: string
{
    case ToDo = 'todo';
    case Doing = 'doing';
    case Done = 'done';
}
