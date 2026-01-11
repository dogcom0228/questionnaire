<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liangjin0228\Questionnaire\QuestionnaireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            QuestionnaireServiceProvider::class,
            \Inertia\ServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Add Inertia middleware
        $app['router']->pushMiddlewareToGroup('web', \Liangjin0228\Questionnaire\Tests\Stubs\HandleInertiaRequests::class);
    }
}
