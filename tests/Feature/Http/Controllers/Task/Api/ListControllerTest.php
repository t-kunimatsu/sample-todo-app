<?php

namespace Tests\Feature\Http\Controllers\Task\Api;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ListControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $this->destroyData();
        parent::tearDown();
    }

    /**
     * 正常系テスト
     */
    #[DataProvider('dataProviderNormalCase')]
    public function test_normal_case(array $expected): void
    {
        $this->generateData();
        $response = $this->get('/api/v1/tasks');
        $response->assertStatus(200);
        $this->assertEquals($expected, $response->json());
    }

    /**
     * 正常系テスト（レコードが1件もないケース）
     */
    public function test_normal_case_without_record(): void
    {
        $expected = [];
        $response = $this->get('/api/v1/tasks');
        $response->assertStatus(200);
        $this->assertEquals($expected, $response->json());
    }

    /**
     * @return array
     */
    public static function dataProviderNormalCase(): array
    {
        return [
            'normal_case' => [
                'expected' => [
                    [
                        'id' => 1,
                        'title' => 'title1',
                        'status' => 'done',
                    ],
                    [
                        'id' => 3,
                        'title' => 'title3',
                        'status' => 'doing',
                    ],
                    [
                        'id' => 4,
                        'title' => 'title4',
                        'status' => 'doing',
                    ],
                    [
                        'id' => 5,
                        'title' => 'title5',
                        'status' => 'todo',
                    ],
                    [
                        'id' => 7,
                        'title' => 'title7',
                        'status' => 'todo',
                    ],
                    [
                        'id' => 6,
                        'title' => 'title6',
                        'status' => 'todo',
                    ],
                ],
            ],
        ];
    }

    private function generateData(): void
    {
        Task::query()->create(['id' => 1, 'title' => 'title1', 'status' => 'done', 'order' => 1]);
        Task::query()->create(['id' => 2, 'title' => 'title2', 'status' => 'done', 'order' => 1]);
        Task::query()->create(['id' => 3, 'title' => 'title3', 'status' => 'doing', 'order' => 1]);
        Task::query()->create(['id' => 4, 'title' => 'title4', 'status' => 'doing', 'order' => 1]);
        Task::query()->create(['id' => 5, 'title' => 'title5', 'status' => 'todo', 'order' => 3]);
        Task::query()->create(['id' => 6, 'title' => 'title6', 'status' => 'todo', 'order' => 5]);
        Task::query()->create(['id' => 7, 'title' => 'title7', 'status' => 'todo', 'order' => 4]);

        Task::query()->where('id', 2)->delete();
    }

    private function destroyData(): void
    {
        Task::query()->whereIn('id', range(1, 7))->forceDelete();
    }
}
