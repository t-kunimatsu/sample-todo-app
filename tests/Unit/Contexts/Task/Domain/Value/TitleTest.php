<?php

declare(strict_types=1);

namespace Tests\Unit\Contexts\Task\Domain\Value;

use App\Contexts\Task\Domain\Value\Title;
use Tests\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class TitleTest extends TestCase
{
    /**
     * 正常系テスト
     */
    #[DataProvider('dataProviderNormalCase')]
    public function test_normal(string $title): void
    {
        $title = str_repeat('a', 65535);
        $result = Title::make($title);
        $this->assertSame($title, $result->get());
    }

    /**
     * 異常系テスト
     */
    #[DataProvider('dataProviderErrorCase')]
    public function test_error(string $title, string $message): void
    {
        $title = str_repeat('a', 65536);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("タスク名が長すぎます。");
        Title::make($title);
    }

    /**
     * @return array
     */
    public static function dataProviderNormalCase(): array
    {
        return [
            'min' => [
                'title' => str_repeat('a', 1),
            ],
            'max' => [
                'title' => str_repeat('a', 65535),
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
                'title' => '',
                'message' => 'タイトルを入力してください。',
            ],
            'over' => [
                'title' => str_repeat('a', 65536),
                'message' => 'タイトルが長すぎます。',
            ],
        ];
    }
}
