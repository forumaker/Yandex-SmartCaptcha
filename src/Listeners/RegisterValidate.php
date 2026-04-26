<?php

namespace forumaker\YandexSmartCaptcha\Listeners;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Event\Saving;
use forumaker\YandexSmartCaptcha\Validator\SmartCaptchaValidator;
use Illuminate\Support\Arr;

class RegisterValidate
{
    protected SmartCaptchaValidator $validator;
    protected SettingsRepositoryInterface $settings;

    public function __construct(
        SmartCaptchaValidator $validator,
        SettingsRepositoryInterface $settings
    ) {
        $this->validator = $validator;
        $this->settings = $settings;
    }

    public function handle(Saving $event): void
    {
        // Ровно как в Turnstile:
        // только новая регистрация, только если защита включена,
        // и не мешаем админу создавать пользователей из админки.
        if (
            !$event->user->exists
            && $this->settings->get('forumaker-yandex-smart-captcha.signup')
            && !$event->actor->isAdmin()
        ) {
            $this->validator->assertValid([
                'smart-token' => Arr::get($event->data, 'attributes.smart-token'),
            ]);
        }
    }
}