<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Value;

use App\Contexts\Task\Domain\Enum\Statuses;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class Status
{
    public const RULE = [
        'bail',
        'required',
        'in:' . Statuses::ToDo->value . ',' . Statuses::Doing->value . ',' . Statuses::Done->value,
    ];
    public const MESSAGES = [
        'status.required' => 'ステータスを入力してください。',
        'status.in' => 'ステータスが不正です。',
    ];

    /**
     * @param Statuses $title
     */
    private function __construct(readonly Statuses $status)
    {
    }

    /**
     * @param string $status
     * @return self
     */
    public static function make(string $status): self
    {
        $validator = Validator::make(
            ['status' => $status],
            ['status' => self::RULE],
            self::MESSAGES,
        );
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->messages()->first());
        }
        return new self(Statuses::from($status));
    }

    /**
     * @return Statuses
     */
    public function get(): Statuses
    {
        return $this->status;
    }
}
