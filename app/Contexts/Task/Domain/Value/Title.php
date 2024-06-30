<?php

declare(strict_types=1);

namespace App\Contexts\Task\Domain\Value;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class Title
{
    private const MIN_LENGTH = 1;
    private const MAX_LENGTH = 65535;
    public const RULE = [
        'bail',
        'required',
        'string',
        'min:' . Title::MIN_LENGTH,
        'max:' . Title::MAX_LENGTH,
    ];
    public const MESSAGES = [
        'title.required' => 'タスク名を入力してください。',
        'title.min' => 'タスク名を入力してください。',
        'title.max' => 'タスク名が長すぎます。',
    ];

    /**
     * @param string $title
     */
    private function __construct(readonly string $title)
    {
    }

    /**
     * @param string $title
     * @return self
     */
    public static function make(string $title): self
    {
        $validator = Validator::make(
            ['title' => $title],
            ['title' => self::RULE],
            self::MESSAGES,
        );
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->messages()->first());
        }
        return new self($title);
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return $this->title;
    }
}
