<?php

namespace Tests\Feature\Http\Controllers\Task\Api;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DeleteControllerTest extends TestCase
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
    public function test_normal(): void
    {
        $id = 2;
        $response = $this->delete("/api/v1/tasks/$id");
        $response->assertStatus(200);

        // 削除されたレコードを検証
        $deleted_task = Task::find($id);
        $this->assertNull($deleted_task);
    }

    /**
     * 異常系テスト
     */
    #[DataProvider('dataProviderErrorCase')]
    public function test_error_case(int $id, array $expected): void
    {
        $response = $this->delete("/api/v1/tasks/$id");
        $response->assertStatus($expected['status']);
        $this->assertEquals($expected['errors'], $response->json()['errors']);
    }

    /**
     * @return array
     */
    public static function dataProviderErrorCase(): array
    {
        return [
            'id_deleted' => [
                'id' => 1,
                'expected' => [
                    'status' => 404,
                    'errors' => [
                        'タスクが登録されていません。',
                    ],
                ],
            ],
            'id_not_exists' => [
                'id' => 0,
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

        Task::query()->where('id', 1)->delete();
    }

    private function destroyData(): void
    {
        Task::query()->whereIn('id', range(1, 2))->forceDelete();
    }
}
