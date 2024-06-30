<?php

namespace Tests\Feature\Http\Controllers\Task\Api;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generateData();
    }

    protected function tearDown(): void
    {
        $this->destroyData();
        parent::tearDown();
    }

    /**
     * 正常系テスト（並び順変更なし）
     */
    #[DataProvider('dataProviderNormalCaseNotUpdatePosition')]
    public function test_normal_not_update_position(int $id, array $payload): void
    {
        $response = $this->patch("/api/v1/tasks/$id", $payload);
        $response->assertStatus(200);

        // 更新されたレコードを検証
        $updated_task = Task::find($id);
        $this->assertSame($payload['title'], $updated_task->title);
    }

    /**
     * 
     * 正常系テスト（ステータス変更なし、自身のみ並び順変更）
     */
    #[DataProvider('dataProviderNormalCaseUpdatePosition')]
    public function test_normal_update_position(int $id, array $payload, $expected_order): void
    {
        $response = $this->patch("/api/v1/tasks/$id", $payload);
        $response->assertStatus(200);

        // 更新されたレコードを検証
        $updated_task = Task::find($id);
        $this->assertSame($expected_order, $updated_task->order);
    }

    /**
     * 
     * 正常系テスト（ステータス変更あり、すべての並び順変更）
     */
    #[DataProvider('dataProviderNormalCaseResetAllOrder')]
    public function test_normal_update_all_order(int $id, array $payload, $expected): void
    {
        $response = $this->patch("/api/v1/tasks/$id", $payload);
        $response->assertStatus(200);

        // 更新されたレコードを検証
        foreach ($expected as $task) {
            $updated_task = Task::find($task['id']);
            $this->assertSame($task['title'], $updated_task->title);
            $this->assertSame($task['status'], $updated_task->status);
            $this->assertSame($task['order'], $updated_task->order);
        }
    }

    /**
     * 異常系テスト
     */
    #[DataProvider('dataProviderErrorCase')]
    public function test_error_case(int $id, array $payload, array $expected): void
    {
        $response = $this->patch("/api/v1/tasks/$id", $payload);
        $response->assertStatus($expected['status']);
        $this->assertEquals($expected['errors'], $response->json()['errors']);
    }

    /**
     * @return array
     */
    public static function dataProviderNormalCaseNotUpdatePosition(): array
    {
        return [
            'normal_case' => [
                'id' => 2,
                'payload' => [
                    'title' => 'title2_updated',
                    'status' => 'todo',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function dataProviderNormalCaseUpdatePosition(): array
    {
        return [
            'down' => [
                'id' => 4,
                'payload' => [
                    'title' => 'title4',
                    'status' => 'done',
                    'position' => 1,
                ],
                'expected_order' => 200 + 65535,
            ],
            'up' => [
                'id' => 5,
                'payload' => [
                    'title' => 'title5',
                    'status' => 'done',
                    'position' => 0,
                ],
                'expected_order' => intdiv(100, 2),
            ],
        ];
    }

    /**
     * @return array
     */
    public static function dataProviderNormalCaseResetAllOrder(): array
    {
        return [
            'normal_case' => [
                'id' => 4,
                'payload' => [
                    'title' => 'title4',
                    'status' => 'todo',
                    'position' => 1,
                ],
                'expected' => [
                    [
                        'id' => 2,
                        'title' => 'title2',
                        'status' => 'todo',
                        'order' => 65535 * 1,
                    ],
                    [
                        'id' => 4,
                        'title' => 'title4',
                        'status' => 'todo',
                        'order' => 65535 * 2,
                    ],
                    [
                        'id' => 3,
                        'title' => 'title3',
                        'status' => 'todo',
                        'order' => 65535 * 3,
                    ],
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
            'title_too_long' => [
                'id' => 2,
                'payload' => [
                    'title' => str_repeat('a', 65536),
                    'status' => 'todo',
                ],
                'expected' => [
                    'status' => 400,
                    'errors' => [
                        'タスク名が長すぎます。',
                    ],
                ],
            ],
            'status_invalid' => [
                'id' => 2,
                'payload' => [
                    'title' => str_repeat('a', 65535),
                    'status' => 'imvalid',
                ],
                'expected' => [
                    'status' => 400,
                    'errors' => [
                        'ステータスが不正です。',
                    ],
                ],
            ],
            'id_deleted' => [
                'id' => 1,
                'payload' => [
                    'title' => str_repeat('a', 100),
                    'status' => 'todo',
                ],
                'expected' => [
                    'status' => 404,
                    'errors' => [
                        'タスクが登録されていません。',
                    ],
                ],
            ],
            'id_not_exists' => [
                'id' => 0,
                'payload' => [
                    'title' => str_repeat('a', 100),
                    'status' => 'todo',
                ],
                'expected' => [
                    'status' => 404,
                    'errors' => [
                        'タスクが登録されていません。',
                    ],
                ],
            ],
        ];
    }

    private function generateData(): void
    {
        Task::query()->create(['id' => 1, 'title' => 'title1', 'status' => 'doing', 'order' => 2]);
        Task::query()->create(['id' => 2, 'title' => 'title2', 'status' => 'todo', 'order' => 10]);
        Task::query()->create(['id' => 3, 'title' => 'title3', 'status' => 'todo', 'order' => 20]);
        Task::query()->create(['id' => 4, 'title' => 'title4', 'status' => 'done', 'order' => 100]);
        Task::query()->create(['id' => 5, 'title' => 'title5', 'status' => 'done', 'order' => 200]);

        Task::query()->where('id', 1)->delete();
    }

    private function destroyData(): void
    {
        Task::query()->whereIn('id', range(1, 5))->forceDelete();
    }
}
