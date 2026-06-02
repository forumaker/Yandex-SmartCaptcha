<?php

namespace forumaker\YandexSmartCaptcha\Listeners;

use Flarum\Forum\LogInValidator;
use Flarum\Foundation\AbstractValidator;
use Flarum\Settings\SettingsRepositoryInterface;
use forumaker\YandexSmartCaptcha\SmartCaptcha\SmartCaptcha;
use forumaker\YandexSmartCaptcha\Validator\SmartCaptchaValidator;
use Illuminate\Validation\Validator;

class AddValidatorRule
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected SmartCaptcha $captcha
    ) {
    }

    public function __invoke(AbstractValidator $flarumValidator, Validator $validator): void
    {
        $validator->addExtension(
            'smartcaptcha',
            function ($attribute, $value) {
                if (!is_string($value) || $value === '') {
                    return false;
                }

                $ip = app()->bound('forumaker.captcha.ip')
                    ? app('forumaker.captcha.ip')
                    : null;

                return $this->captcha->verify($value, $ip);
            }
        );

        if (
            $flarumValidator instanceof SmartCaptchaValidator
            && $this->settings->get('forumaker-yandex-smart-captcha.signup')
        ) {
            $validator->addRules([
                'smart-token' => ['required', 'smartcaptcha'],
            ]);
        }

        if (
            $flarumValidator instanceof LogInValidator
            && $this->settings->get('forumaker-yandex-smart-captcha.signin')
        ) {
            $validator->addRules([
                'smart-token' => ['required', 'smartcaptcha'],
            ]);
        }
    }
}
