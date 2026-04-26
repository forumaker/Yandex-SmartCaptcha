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
    protected SettingsRepositoryInterface $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(AbstractValidator $flarumValidator, Validator $validator): void
    {
        $secret = $this->settings->get('forumaker-yandex-smart-captcha.server_key');

        $validator->addExtension(
            'smartcaptcha',
            function ($attribute, $value) use ($secret) {
                if (!is_string($value) || !is_string($secret) || $secret === '') {
                    return false;
                }

                return !empty($value) && (new SmartCaptcha($secret))->verify($value);
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