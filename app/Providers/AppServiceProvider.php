<?php

namespace App\Providers;

use App\Contexts\Task\Domain\Factory\TaskFactory;
use App\Contexts\Task\Domain\Persistence\TaskRepository;
use App\Contexts\Task\Infrastructure\Persistence\TaskRepositoryImpl;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TaskRepository::class,
            fn ($app) => new TaskRepositoryImpl($app->make(TaskFactory::class))
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
