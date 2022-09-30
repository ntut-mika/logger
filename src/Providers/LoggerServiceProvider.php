<?php

namespace Mika\Logger\Providers;

use Mika\Architecture\Bases\BaseServiceProvider;
use Mika\Architecture\Providers\ArchitectureServiceProvider;
use Mika\Logger\Providers\LoggerEventServiceProvider;
use Mika\Logger\Providers\LoggerRouteServiceProvider;

class LoggerServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->register(ArchitectureServiceProvider::class);
        $this->app->register(LoggerRouteServiceProvider::class);
        $this->app->register(LoggerEventServiceProvider::class);
    }

    public function boot()
    {
        $this->loadConfigsAndPublishFrom(__DIR__ . '/../../config');
        $this->loadMigrationsAndPublishFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsAndPublishFrom(__DIR__ . '/../../resources/views', 'logger');
    }
}
