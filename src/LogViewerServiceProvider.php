<?php

namespace Redteaman\LogViewer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class LogViewerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        // ✅ 改用這種方式包裝 API，明確指定 middleware 為 api
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__.'/../routes/api.php');

        $this->loadViewsFrom(__DIR__.'/Views', 'log_viewer');
    }

    public function register()
    {
        $this->app->singleton('files', fn () => new \Illuminate\Filesystem\Filesystem);
    }
}
