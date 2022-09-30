<?php

namespace Mika\Logger\Test;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mika\Logger\Providers\LoggerServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            LoggerServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/Stubs/Migrations');
    }
}
