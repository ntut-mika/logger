<?php

namespace Mika\Logger\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Mika\Logger\Listeners\ModelListener;
use Mika\Logger\Listeners\RequestListener;

class LoggerEventServiceProvider extends EventServiceProvider
{
    protected $subscribe = [
        RequestListener::class,
        ModelListener::class,
    ];
}
