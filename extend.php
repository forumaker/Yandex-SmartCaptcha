<?php

namespace forumaker\YandexSmartCaptcha;

use Flarum\Extend;
use Flarum\Forum\LogInValidator;
use Flarum\User\Event\Saving as UserSaving;
use forumaker\YandexSmartCaptcha\Listeners\AddValidatorRule;
use forumaker\YandexSmartCaptcha\Listeners\RegisterValidate;
use forumaker\YandexSmartCaptcha\Validator\SmartCaptchaValidator;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/resources/less/admin.less'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\Settings())
        ->default('forumaker-yandex-smart-captcha.site_key', '')
        ->default('forumaker-yandex-smart-captcha.server_key', '')
        ->default('forumaker-yandex-smart-captcha.signup', true)
        ->default('forumaker-yandex-smart-captcha.signin', false)
        ->serializeToForum(
            'forumaker-yandex-smart-captcha.site_key',
            'forumaker-yandex-smart-captcha.site_key'
        )
        ->serializeToForum(
            'forumaker-yandex-smart-captcha.signup',
            'forumaker-yandex-smart-captcha.signup',
            'boolval'
        )
        ->serializeToForum(
            'forumaker-yandex-smart-captcha.signin',
            'forumaker-yandex-smart-captcha.signin',
            'boolval'
        ),

    (new Extend\Validator(SmartCaptchaValidator::class))
        ->configure(AddValidatorRule::class),

    (new Extend\Validator(LogInValidator::class))
        ->configure(AddValidatorRule::class),

    (new Extend\Event())
        ->listen(UserSaving::class, RegisterValidate::class),
];