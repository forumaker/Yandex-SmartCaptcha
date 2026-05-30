<?php

namespace forumaker\YandexSmartCaptcha\Api;

use Flarum\Api\Schema;

class SmartCaptchaFields
{
    public function __invoke(): array
    {
        return [
            Schema\Str::make('smart-token')
                ->writableOnCreate()
                ->nullable()
                ->set(fn () => null),
            Schema\Str::make('smart-action')
                ->writableOnCreate()
                ->nullable()
                ->set(fn () => null),
        ];
    }
}
