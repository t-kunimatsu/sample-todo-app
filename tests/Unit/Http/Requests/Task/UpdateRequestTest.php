<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Task;

use App\Http\Requests\Task\UpdateRequest;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateRequestTest extends TestCase
{
    /**
     * 正常系テスト
     */
    #[DataProvider('dataProviderNormalCase')]
    public function test_normal(array $payload): void
    {
        $request = new UpdateRequest();
        $rules = $request->rules();

        /** @var Validator $validator */
        $validator = validator($payload, $request->rules(), $request->messages());
        $this->assertTrue($validator->passes());
    }

    /**
     * 異常系テスト
     */
    #[DataProvider('dataProviderErrorCase')]
    public function test_error(array $payload, array $errors): void
    {
        $request = new UpdateRequest();

        /** @var Validator $validator */
        $validator = validator($payload, $request->rules(), $request->messages());
        $this->assertFalse($validator->passes());
        $this->assertEquals($errors, $validator->errors()->messages());
    }

    /**
     * @return array
     */
    public static function dataProviderNormalCase(): array
    {
        return [
            'todo' => [
                'payload' => [
                    'title' => str_repeat('a', 65535),
                    'status' => 'todo',
                ],
            ],
            'doing' => [
                'payload' => [
                    'title' => str_repeat('a', 1),
                    'status' => 'doing',
                ],
            ],
            'done' => [
                'payload' => [
                    'title' => str_repeat('a', 100),
                    'status' => 'done',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function dataProviderErrorCase(): array
    {
        return [
            'title_missing' => [
                'payload' => [
                    'status' => 'todo',
                ],
                'errors' => [
                    'title' => ['タスク名を入力してください。'],
                ],
            ],
            'title_too_short' => [
                'payload' => [
                    'title' => '',
                    'status' => 'todo',
                ],
                'errors' => [
                    'title' => ['タスク名を入力してください。'],
                ],
            ],
            'title_too_long' => [
                'payload' => [
                    'title' => str_repeat('a', 65536),
                    'status' => 'todo',
                ],
                'errors' => [
                    'title' => ['タスク名が長すぎます。'],
                ],
            ],
            'status_missing' => [
                'payload' => [
                    'title' => str_repeat('a', 100),
                ],
                'errors' => [
                    'status' => ['ステータスを入力してください。'],
                ],
            ],
            'status_invalid' => [
                'payload' => [
                    'title' => str_repeat('a', 100),
                    'status' => 'unknown',
                ],
                'errors' => [
                    'status' => ['ステータスが不正です。'],
                ],
            ],
            'combination' => [
                'payload' => [
                    'status' => 'unknown',
                ],
                'errors' => [
                    'title' => ['タスク名を入力してください。'],
                    'status' => ['ステータスが不正です。'],
                ],
            ],
        ];
    }
}
