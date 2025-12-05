<?php

namespace Athulr\LaravelQr\Facades;

use Illuminate\Support\Facades\Facade;

class QrCode extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'qr-code';
    }
}
