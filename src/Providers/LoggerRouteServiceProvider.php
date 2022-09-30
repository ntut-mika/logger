<?php

namespace Mika\Logger\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class LoggerRouteServiceProvider extends RouteServiceProvider
{
    public function boot()
    {
        $this->routes(function () {
            Route::middleware('api')->prefix('api')->group(__DIR__ . '/../routes/api.php');
            Route::middleware('web')->group(__DIR__ . '/../routes/web.php');
        });
    }
}
