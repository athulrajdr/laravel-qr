<?php

namespace AthulR\LaravelQr;

use Illuminate\Support\ServiceProvider;
use Athulr\LaravelQr\Services\Generator;

class QrCodeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/qrcode.php', 'qrcode');

        $this->app->singleton('qr-code', function ($app) {
            return new Generator(config('qrcode'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/qrcode.php' => config_path('qrcode.php'),
        ], 'config');
    }
}
