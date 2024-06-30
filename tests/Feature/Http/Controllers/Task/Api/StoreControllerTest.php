<?php

namespace Tests\Feature\Http\Controllers\Task\Api;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StoreControllerTest extends TestCase
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
     * 正常系テスト
     */
    #[DataProvider('dataProviderNormalCase')]
    public function test_normal_case(array $payload, array $expected, int $expected_order): void
    {
        $response = $this->post('/api/v1/tasks', $payload);
        $response->assertStatus(200);
        $this->assertEquals($expected, $response->json());

        // 追加されたレコードのorderを検証
        $new_task = Task::find($expected['id']);
        $this->assertSame($expected_order, $new_task->order);
    }

    /**
     * 異常系テスト
     */
    #[DataProvider('dataProviderErrorCase')]
    public function test_error_case(array $payload, array $expected): void
    {
        $response = $this->post('/api/v1/tasks', $payload);
        $response->assertStatus(400);
        $this->assertEquals($expected, $response->json());
    }

    /**
     * @return array
     */
    public static function dataProviderNormalCase(): array
    {
        return [
            'normal_case' => [
                'payload' => [
                    'title' => 'title_new',
                    'status' => 'todo',
                ],
                'expected' => [
                    'id' => 4,
                    'title' => 'title_new',
                    'status' => 'todo',
                ],
                'expected_order' => 2000 + 65535,
            ],
            'not_exists_status_record_case' => [
                'payload' => [
                    'title' => 'title_new',
                    'status' => 'doing',
                ],
                'expected' => [
                    'id' => 5,
                    'title' => 'title_new',
                    'status' => 'doing',
                ],
                'expected_order' => 65535,
            ],
        ];
    }


    /**
     * @return array
     */
    public static function dataProviderErrorCase(): array
    {
        return [
            'combination' => [
                'payload' => [
                    'title' => str_repeat('a', 65536),
                    'status' => 'invalid',
                ],
                'expected' => [
                    'errors' => [
                        'タスク名が長すぎます。',
                        'ステータスが不正です。',
                    ],
                ],
            ],
        ];
    }

    private function generateData(): void
    {
        Task::query()->create(['id' => 1, 'title' => 'title1', 'status' => 'doing', 'order' => 65535]);
        Task::query()->create(['id' => 2, 'title' => 'title2', 'status' => 'todo', 'order' => 1000]);
        Task::query()->create(['id' => 3, 'title' => 'title3', 'status' => 'todo', 'order' => 2000]);

        Task::query()->where('id', 1)->delete();
    }

    private function destroyData(): void
    {
        Task::query()->whereIn('id', range(1, 3))->forceDelete();
    }
}
