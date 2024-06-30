<?php

declare(strict_types=1);

namespace Tests\Unit\Contexts\Task\Domain\Factory;

use App\Contexts\Task\Domain\Entity\Task;
use App\Contexts\Task\Domain\Entity\TaskList;
use App\Contexts\Task\Domain\Exception\GapThresholdException;
use App\Contexts\Task\Domain\Service\OrderCalculateService;
use App\Contexts\Task\Domain\Value\Status;
use App\Contexts\Task\Domain\Value\Title;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderCalculateServiceTest extends TestCase
{
    /**
     * getOrderの正常系テスト
     */
    #[DataProvider('dataProviderGetOrderNormalCase')]
    public function test_getOrder_normal(int $position, TaskList $task_list, int $expected): void
    {
        $order_calculate_service = new OrderCalculateService();
        $order = $order_calculate_service->getOrder($position, $task_list);
        $this->assertSame($expected, $order);
    }

    /**
     * getOrderの異常系テスト
     */
    #[DataProvider('dataProviderGetOrderErrorCase')]
    public function test_getOrder_error(int $position, TaskList $task_list): void
    {
        $this->expectException(GapThresholdException::class);
        $order_calculate_service = new OrderCalculateService();
        $order_calculate_service->getOrder($position, $task_list);
    }

    /**
     * getOrdersForResetAllの正常系テスト
     */
    #[DataProvider('dataProviderGetOrdersForResetAllNormalCase')]
    public function test_getOrdersForResetAll_normal(int $count, array $expected): void
    {
        $order_calculate_service = new OrderCalculateService();
        $orders = $order_calculate_service->getOrdersForResetAll($count);
        $this->assertSame($expected, $orders);
    }

    /**
     * checkの正常系テスト
     */
    #[DataProvider('dataProviderCheckNormalCase')]
    public function test_check_normal(?Task $previous, ?Task $next): void
    {
        $order_calculate_service = new OrderCalculateService();
        $result = $order_calculate_service->check($previous, $next);
        $this->assertNull($result);
    }

    /**
     * checkの異常系テスト
     */
    #[DataProvider('dataProviderCheckErrorCase')]
    public function test_check_error(?Task $previous, ?Task $next): void
    {
        $this->expectException(GapThresholdException::class);
        $order_calculate_service = new OrderCalculateService();
        $order_calculate_service->check($previous, $next);
    }

    /**
     * @return array
     */
    public static function dataProviderGetOrderNormalCase(): array
    {
        return [
            'new' => [
                'position' => 0,
                'task_list' => TaskList::make([]),
                'expected' => 65535,
            ],
            'head' => [
                'position' => 0,
                'task_list' => TaskList::make([
                    Task::make(
                        1,
                        self::makeTitle('title'),
                        self::makeStatus('todo'),
                        10000,
                    ),
                ]),
                'expected' => 5000,
            ],
            'tail' => [
                'position' => 1,
                'task_list' => TaskList::make([
                    Task::make(
                        1,
                        self::makeTitle('title'),
                        self::makeStatus('todo'),
                        10000,
                    ),
                ]),
                'expected' => 10000 + 65535,
            ],
            'between1' => [
                'position' => 1,
                'task_list' => TaskList::make([
                    Task::make(
                        1,
                        self::makeTitle('title'),
                        self::makeStatus('todo'),
                        500,
                    ),
                    Task::make(
                        2,
                        self::makeTitle('title'),
                        self::makeStatus('todo'),
                        1000,
                    ),
                    Task::make(
                        3,
                        self::makeTitle('title'),
                        self::makeStatus('todo'),
                        1601,
                    ),
                ]),
                'expected' => 750,
            ],
            'between2' => [
                'position' => 2,
                'task_list' => TaskList::make([
                    Task::make(
                        1,
                        Title::make('title'),
                        self::makeStatus('todo'),
                        500,
                    ),
                    Task::make(
                        2,
                        Title::make('title'),
                        self::makeStatus('todo'),
                        1000,
                    ),
                    Task::make(
                        3,
                        Title::make('title'),
                        self::makeStatus('todo'),
                        1601,
                    ),
                ]),
                'expected' => 1300,
            ],
        ];
    }

    /**
     * @return array
     */
    public static function dataProviderGetOrderErrorCase(): array
    {
        return [
            'gapMinThrehold' => [
                'position' => 1,
                'task_list' => TaskList::make([
                    Task::make(
                        1,
                        Title::make('title'),
                        self::makeStatus('todo'),
                        990,
                    ),
                    Task::make(
                        2,
                        Title::make('title'),
                        self::makeStatus('todo'),
                        1000,
                    ),
                    Task::make(
                        3,
                        Title::make('title'),
                        self::makeStatus('todo'),
                        1601,
                    ),
                ]),
            ],
        ];
    }

    /**
     * @return array
     */
    public static function dataProviderGetOrdersForResetAllNormalCase(): array
    {
        return [
            'count5' => [
                'count' => 5,
                'expected' => [
                    65535 * 1,
                    65535 * 2,
                    65535 * 3,
                    65535 * 4,
                    65535 * 5,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function dataProviderCheckNormalCase(): array
    {
        return [
            'bothNull' => [
                'previous' => null,
                'next' => null,
            ],
            'nextNull' => [
                'previous' => Task::make(
                    1,
                    Title::make('title'),
                    self::makeStatus('todo'),
                    9,
                ),
                'next' => null,
            ],
            'previousNull' => [
                'previous' => null,
                'next' => Task::make(
                    1,
                    Title::make('title'),
                    self::makeStatus('todo'),
                    11,
                ),
            ],
            'between' => [
                'previous' => Task::make(
                    1,
                    Title::make('title'),
                    self::makeStatus('todo'),
                    10,
                ),
                'next' => Task::make(
                    2,
                    Title::make('title'),
                    self::makeStatus('todo'),
                    21,
                ),
            ],
        ];
    }

    /**
     * @return array
     */
    public static function dataProviderCheckErrorCase(): array
    {
        return [
            'previousNull' => [
                'previous' => null,
                'next' => Task::make(
                    1,
                    Title::make('title'),
                    self::makeStatus('todo'),
                    10,
                ),
            ],
            'between' => [
                'previous' => Task::make(
                    1,
                    Title::make('title'),
                    self::makeStatus('todo'),
                    10,
                ),
                'next' => Task::make(
                    2,
                    Title::make('title'),
                    self::makeStatus('todo'),
                    20,
                ),
            ],
        ];
    }

    /**
     * @param string $title
     * @return Title
     */
    private static function makeTitle(string $title): Title
    {
        // DataProviderアトリビュートはstaticメソッドである必要があるが、
        // DataProviderメソッドが実行されるタイミングではファサードを取得できない
        // （setUpBeforeClassよりも前に実行されるのでどうしようもない・・・）
        // Title::make内部のValidatorファサードが取得できないのでモック化する
        Validator::shouldReceive('make')
            ->withAnyArgs()
            ->andReturnSelf();
        Validator::shouldReceive('fails')
            ->andReturnFalse();
        return Title::make($title);
    }

    /**
     * @param string $status
     * @return Status
     */
    private static function makeStatus(string $status): Status
    {
        // Titleと同様、Validatorをモック化
        Validator::shouldReceive('make')
            ->withAnyArgs()
            ->andReturnSelf();
        Validator::shouldReceive('fails')
            ->andReturnFalse();
        return Status::make($status);
    }
}
