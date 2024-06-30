<?php

declare(strict_types=1);

namespace Tests\Unit\Contexts\Task\Domain\Value;

use App\Contexts\Task\Domain\Value\Status;
use Tests\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class StatusTest extends TestCase
{
    /**
     * 正常系テスト
     */
    #[DataProvider('dataProviderNormalCase')]
    public function test_normal(string $status): void
    {
        $result = Status::make($status);
        $this->assertSame($status, $result->get()->value);
    }

    /**
     * 異常系テスト
     */
    #[DataProvider('dataProviderErrorCase')]
    public function test_error(string $status, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        Status::make($status);
    }

    /**
     * @return array
     */
    public static function dataProviderNormalCase(): array
    {
        return [
            'todo' => [
                'status' => 'todo',
            ],
            'doing' => [
                'status' => 'doing',
            ],
            'done' => [
                'status' => 'done',
            ],
        ];
    }

    /**
     * @return array
     */
    public static function dataProviderErrorCase(): array
    {
        return [
            'empty' => [
                'status' => '',
                'message' => 'ステータスを入力してください。',
            ],
            'invalid' => [
                'status' => 'invalid',
                'message' => 'ステータスが不正です。',
            ],
        ];
    }
}
